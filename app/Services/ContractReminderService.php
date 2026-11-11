<?php

namespace App\Services;

use App\Mail\ContractReminderMail;
use App\Models\ReminderRecipient;
use App\Models\RuangLingkup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContractReminderService
{
    protected array $milestones = [90, 30, 7, 0];

    public function sendRemindersForAllContracts(): int
    {
        $expiringModels = [];

        RuangLingkup::where('status', 'aktif')
            ->where('approval_status', 'approved')
            ->whereDate('tanggal_berakhir', '>=', now()->startOfDay())
            ->chunkById(100, function ($contracts) use (&$expiringModels) {
                foreach ($contracts as $contract) {
                    if ($this->shouldSendReminder($contract)) {
                        $expiringModels[] = $contract;
                    }
                }
            });

        $expiredModels = [];

        RuangLingkup::where('status', 'tidak_aktif')
            ->where('approval_status', 'approved')
            ->whereDate('tanggal_berakhir', '>=', now()->subDays(7)->startOfDay())
            ->chunkById(100, function ($contracts) use (&$expiredModels) {
                foreach ($contracts as $contract) {
                    if ($this->shouldSendExpiredReminder($contract)) {
                        $expiredModels[] = $contract;
                    }
                }
            });

        if (empty($expiringModels) && empty($expiredModels)) {
            Log::info('Reminder: tidak ada kontrak yang perlu reminder.');

            return 0;
        }

        $recipients = ReminderRecipient::where('is_active', true)->get();

        if ($recipients->isEmpty()) {
            Log::info('Reminder: tidak ada penerima aktif.');

            return 0;
        }

        $expiringData = array_map(fn ($c) => $this->contractToArray($c), $expiringModels);
        $expiredData = array_map(fn ($c) => $this->contractToArray($c), $expiredModels);

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->queue(
                    new ContractReminderMail(
                        expiring: $expiringData,
                        expired: $expiredData,
                        purpose: $recipient->purpose ?? 'admin'
                    )
                );
            } catch (\Throwable $e) {
                Log::error('Gagal queue reminder digest email', [
                    'recipient' => $recipient->email,
                    'expiring_count' => count($expiringModels),
                    'expired_count' => count($expiredModels),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        foreach ($expiringModels as $contract) {
            $daysRemaining = (int) $contract->days_remaining;
            $matchedMilestone = collect($this->milestones)->first(fn ($m) => $daysRemaining >= $m);

            $contract->contractHistory()->create([
                'action_type' => 'reminder_sent',
                'old_status' => $contract->status,
                'new_status' => $contract->status,
                'notes' => "Sent H-{$matchedMilestone} reminder (digest)",
            ]);
        }

        foreach ($expiredModels as $contract) {
            $contract->contractHistory()->create([
                'action_type' => 'expired_reminder_sent',
                'old_status' => 'tidak_aktif',
                'new_status' => 'tidak_aktif',
                'notes' => 'Sent expired reminder (digest)',
            ]);
        }

        Log::info('Reminder digest berhasil dikirim', [
            'recipients' => $recipients->count(),
            'expiring' => count($expiringModels),
            'expired' => count($expiredModels),
        ]);

        return count($expiringModels) + count($expiredModels);
    }

    public function sendReminderForContract(RuangLingkup $contract): bool
    {
        $isExpired = $contract->status === 'tidak_aktif';

        if ($isExpired) {
            if (! $this->shouldSendExpiredReminder($contract)) {
                return false;
            }
        } elseif (! $this->shouldSendReminder($contract)) {
            return false;
        }

        $recipients = ReminderRecipient::where('is_active', true)->get();

        if ($recipients->isEmpty()) {
            return false;
        }

        $expiringData = $isExpired ? [] : [$this->contractToArray($contract)];
        $expiredData = $isExpired ? [$this->contractToArray($contract)] : [];

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->queue(
                    new ContractReminderMail(
                        expiring: $expiringData,
                        expired: $expiredData,
                        purpose: $recipient->purpose ?? 'admin'
                    )
                );
            } catch (\Throwable $e) {
                Log::error('Gagal queue reminder email (single)', [
                    'contract_id' => $contract->id,
                    'recipient' => $recipient->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($isExpired) {
            $contract->contractHistory()->create([
                'action_type' => 'expired_reminder_sent',
                'old_status' => 'tidak_aktif',
                'new_status' => 'tidak_aktif',
                'notes' => 'Sent expired reminder (digest)',
            ]);
        } else {
            $daysRemaining = (int) $contract->days_remaining;
            $matchedMilestone = collect($this->milestones)->first(fn ($m) => $daysRemaining >= $m);

            $contract->contractHistory()->create([
                'action_type' => 'reminder_sent',
                'old_status' => $contract->status,
                'new_status' => $contract->status,
                'notes' => "Sent H-{$matchedMilestone} reminder (digest)",
            ]);
        }

        return true;
    }

    private function contractToArray(RuangLingkup $contract): array
    {
        return [
            'id' => $contract->id,
            'nama_asuransi' => $contract->nama_asuransi,
            'penjamin_category' => $contract->penjamin_category,
            'badan_usaha' => $contract->badanUsahaPivot?->first()?->badanUsaha?->nama_bu ?? '-',
            'tanggal_mulai' => $contract->tanggal_mulai?->translatedFormat('d M Y') ?? '-',
            'tanggal_berakhir' => $contract->tanggal_berakhir?->translatedFormat('d M Y') ?? '-',
            'days_remaining' => (int) $contract->days_remaining,
            'url' => route('admin.ruang-lingkup.show', $contract),
        ];
    }

    private function shouldSendReminder(RuangLingkup $contract): bool
    {
        if ($contract->status !== 'aktif') {
            return false;
        }

        $daysRemaining = (int) $contract->days_remaining;

        if ($daysRemaining < 0) {
            return false;
        }

        $matchedMilestone = collect($this->milestones)->first(fn ($m) => $daysRemaining >= $m);

        if ($matchedMilestone === null) {
            return false;
        }

        $historyExists = $contract->contractHistory()
            ->where('action_type', 'reminder_sent')
            ->where(function ($q) use ($matchedMilestone) {
                $q->where('notes', "Sent H-{$matchedMilestone} reminder")
                  ->orWhere('notes', "Sent H-{$matchedMilestone} reminder (digest)");
            })
            ->exists();

        if ($historyExists) {
            return false;
        }

        return true;
    }

    private function shouldSendExpiredReminder(RuangLingkup $contract): bool
    {
        if ($contract->status !== 'tidak_aktif') {
            return false;
        }

        if (! $contract->is_expired) {
            return false;
        }

        return ! $contract->contractHistory()
            ->where('action_type', 'expired_reminder_sent')
            ->exists();
    }
}
