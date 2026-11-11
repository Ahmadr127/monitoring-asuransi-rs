<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContractReminderMail;
use App\Models\ReminderRecipient;
use App\Models\RuangLingkup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReminderRecipientController extends Controller
{
    public function index()
    {
        $recipients = ReminderRecipient::all();

        return view('admin.settings.reminder-recipients', compact('recipients'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:reminder_recipients,email,NULL,id,category,'.$request->category,
                'name' => 'required|string|max:255',
                'category' => 'required|in:internal,manual',
                'purpose' => 'required|in:admin,legal,marketing,farmasi,kasir,pendaftaran',
                'insurance_name' => 'nullable|string|max:255',
            ]);

            ReminderRecipient::create($request->only(['email', 'name', 'category', 'purpose', 'insurance_name']));

            return redirect()->back()->with('success', 'Penerima pengingat berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan penerima: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal menambahkan penerima: '.$e->getMessage());
        }
    }

    public function update(Request $request, ReminderRecipient $recipient)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'name' => 'required|string|max:255',
                'purpose' => 'required|in:admin,legal,marketing,farmasi,kasir,pendaftaran',
                'is_active' => 'boolean',
            ]);

            $recipient->update($request->only(['email', 'name', 'purpose', 'is_active']));

            return redirect()->back()->with('success', 'Data penerima berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal mengupdate penerima: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal mengupdate penerima: '.$e->getMessage());
        }
    }

    public function destroy(ReminderRecipient $recipient)
    {
        try {
            $recipient->delete();

            return redirect()->back()->with('success', 'Penerima berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus penerima: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus penerima: '.$e->getMessage());
        }
    }

    public function toggleStatus(ReminderRecipient $recipient)
    {
        try {
            $recipient->update(['is_active' => ! $recipient->is_active]);

            return redirect()->back()->with('success', 'Status penerima berhasil diubah.');
        } catch (\Exception $e) {
            Log::error('Gagal mengubah status penerima: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal mengubah status penerima: '.$e->getMessage());
        }
    }

    public function sendManual(ReminderRecipient $recipient)
    {
        try {
            $expiring = RuangLingkup::where('status', 'aktif')
                ->whereDate('tanggal_berakhir', '>=', now()->startOfDay())
                ->get()
                ->filter(function ($contract) {
                    return $contract->days_remaining <= 90;
                });

            $expired = RuangLingkup::where('status', 'tidak_aktif')
                ->whereDate('tanggal_berakhir', '>=', now()->subDays(7)->startOfDay())
                ->get()
                ->filter(fn ($c) => $c->is_expired);

            if ($expiring->isEmpty() && $expired->isEmpty()) {
                return redirect()->back()->with('info', 'Tidak ada kontrak yang mendekati jatuh tempo (H-90) atau yang baru berakhir untuk penerima ini.');
            }

            $mapData = fn ($c) => [
                'id' => $c->id,
                'nama_asuransi' => $c->nama_asuransi,
                'penjamin_category' => $c->penjamin_category,
                'badan_usaha' => $c->badanUsahaPivot?->first()?->badanUsaha?->nama_bu ?? '-',
                'tanggal_mulai' => $c->tanggal_mulai?->translatedFormat('d M Y') ?? '-',
                'tanggal_berakhir' => $c->tanggal_berakhir?->translatedFormat('d M Y') ?? '-',
                'days_remaining' => (int) $c->days_remaining,
                'url' => route('admin.ruang-lingkup.show', $c),
            ];

            Mail::to($recipient->email)->queue(new ContractReminderMail(
                expiring: $expiring->map($mapData)->toArray(),
                expired: $expired->map($mapData)->toArray(),
                purpose: $recipient->purpose ?? 'admin'
            ));

            foreach ($expiring as $contract) {
                $contract->contractHistory()->create([
                    'action_type' => 'reminder_sent',
                    'old_status' => $contract->status,
                    'new_status' => $contract->status,
                    'notes' => "Manual H-{$contract->days_remaining} reminder (digest)",
                ]);
            }

            foreach ($expired as $contract) {
                $contract->contractHistory()->create([
                    'action_type' => 'expired_reminder_sent',
                    'old_status' => 'tidak_aktif',
                    'new_status' => 'tidak_aktif',
                    'notes' => 'Manual expired reminder (digest)',
                ]);
            }

            $recipient->update(['last_manual_sent_at' => now()]);

            $total = $expiring->count() + $expired->count();

            return redirect()->back()->with('success', "Email pengingat berhasil dikirim manual ke {$recipient->email} ({$total} kontrak).");
        } catch (\Exception $e) {
            Log::error('Gagal mengirim reminder manual: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Gagal mengirim reminder: '.$e->getMessage());
        }
    }
}
