<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Exceptions\WorkflowException;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\RuangLingkup;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStep;
use Illuminate\Support\Facades\DB;

/**
 * Engine state-machine approval berjenjang yang dinamis.
 *
 * Clean architecture: layer service memegang seluruh aturan workflow,
 * controller hanya meneruskan HTTP request ke sini.
 */
class WorkflowService
{
    /**
     * Mulai pengajuan persetujuan untuk sebuah kontrak (draft → in_review).
     */
    public function start(RuangLingkup $contract, User $requestor): ApprovalRequest
    {
        if ($contract->approval_status->value === ApprovalStatus::APPROVED->value) {
            throw new WorkflowException('Kontrak sudah disetujui, tidak dapat mengajukan ulang.');
        }

        $workflow = WorkflowDefinition::query()
            ->where('is_active', true)
            ->with('steps')
            ->first();

        if (! $workflow || $workflow->steps->isEmpty()) {
            throw new WorkflowException('Belum ada workflow aktif yang terkonfigurasi.');
        }

        return DB::transaction(function () use ($contract, $requestor, $workflow) {
            $request = $workflow->requests()->create([
                'subject_type' => $contract::class,
                'subject_id' => $contract->id,
                'requested_by' => $requestor->id,
                'current_step' => 1,
                'status' => 'in_review',
                'submitted_at' => now(),
            ]);

            $nextPendingOrder = null;

            foreach ($workflow->steps as $step) {
                $approverId = $this->resolveApproverUserId($step, $requestor);

                $status = 'pending';
                if ($this->shouldSkip($step, $contract, $requestor)) {
                    $status = 'skipped';
                } elseif ($approverId && (int) $approverId === (int) $requestor->id) {
                    // anti self-approval
                    $status = 'skipped';
                }

                $request->steps()->create([
                    'workflow_step_id' => $step->id,
                    'step_order' => $step->step_order,
                    'title' => $step->name,
                    'approver_type' => $step->approver_type,
                    'approver_value' => $step->approver_value,
                    'approver_user_id' => $approverId,
                    'status' => $status,
                ]);

                if ($status === 'pending' && $nextPendingOrder === null) {
                    $nextPendingOrder = $step->step_order;
                }
            }

            $request->update(['current_step' => $nextPendingOrder ?? 1]);

            $contract->update([
                'approval_status' => ApprovalStatus::IN_REVIEW,
                'submitted_by' => $requestor->id,
                'current_approval_request_id' => $request->id,
            ]);

            $this->logHistory($contract, 'submitted', null, 'kontrak', $requestor, 'Pengajuan persetujuan kontrak');

            // Jika seluruh step terlewat (skip/self-approval) → langsung final.
            if ($this->pendingSteps($request)->isEmpty()) {
                $this->finalizeApproved($request, $requestor, 'Seluruh step dilalui otomatis (skip/self-approval).');
            }

            return $request->fresh(['steps']);
        });
    }

    /**
     * Approve step saat ini; jika semua step selesai → kontrak menjadi aktif.
     */
    public function approve(ApprovalRequest $request, User $user, ?string $note = null): ApprovalRequest
    {
        if ($request->status === 'approved' || $request->status === 'rejected' || $request->status === 'cancelled') {
            throw new WorkflowException('Pengajuan sudah ditutup ('.strtoupper($request->status).').');
        }

        $step = $this->currentPendingStep($request);

        if (! $this->isApprover($request, $step, $user)) {
            throw new WorkflowException('Anda tidak berhak menyetujui langkah ini.');
        }

        return DB::transaction(function () use ($request, $step, $user, $note) {
            $step->update([
                'status' => 'approved',
                'note' => $note,
                'decided_by' => $user->id,
                'decided_at' => now(),
            ]);

            $contract = $request->subject;

            $this->logHistory($contract, 'step_approved', null, 'approval',
                $user, 'Langkah disetujui: '.$step->title);

            $next = $this->pendingSteps($request)->sortBy('step_order')->first();

            if ($next) {
                $request->update(['current_step' => $next->step_order]);

                return $request->fresh(['steps']);
            }

            $this->finalizeApproved($request, $user, 'Semua langkah disetujui');

            return $request->fresh(['steps']);
        });
    }

    /**
     * Tolak pengajuan pada step saat ini → status rejected, kembali ke pengaju.
     */
    public function reject(ApprovalRequest $request, User $user, ?string $note = null): ApprovalRequest
    {
        $step = $this->currentPendingStep($request);

        if (! $this->isApprover($request, $step, $user)) {
            throw new WorkflowException('Anda tidak berhak menolak langkah ini.');
        }

        return DB::transaction(function () use ($request, $step, $user, $note) {
            $step->update([
                'status' => 'rejected',
                'note' => $note,
                'decided_by' => $user->id,
                'decided_at' => now(),
            ]);

            $request->update([
                'status' => 'rejected',
                'decided_at' => now(),
            ]);

            $contract = $request->subject;

            if ($contract) {
                $contract->update(['approval_status' => ApprovalStatus::REJECTED]);
                $this->logHistory($contract, 'rejected', null, 'approval', $user,
                    'Pengajuan ditolak pada langkah: '.$step->title.($note ? " — $note" : ''));
            }

            return $request->fresh(['steps']);
        });
    }

    /**
     * Batalkan pengajuan (oleh pengaju / admin) → kembali ke draft.
     */
    public function cancel(ApprovalRequest $request, User $user): ApprovalRequest
    {
        if ($request->status !== 'in_review') {
            throw new WorkflowException('Hanya pengajuan yang sedang berjalan yang dapat dibatalkan.');
        }

        if ((int) $request->requested_by !== (int) $user->id && ! $user->isAdmin()) {
            throw new WorkflowException('Anda tidak berhak membatalkan pengajuan ini.');
        }

        return DB::transaction(function () use ($request, $user) {
            $request->steps()->where('status', 'pending')->update([
                'status' => 'cancelled',
                'decided_by' => $user->id,
                'decided_at' => now(),
            ]);

            $request->update([
                'status' => 'cancelled',
                'decided_at' => now(),
            ]);

            $contract = $request->subject;

            if ($contract) {
                $contract->update([
                    'approval_status' => ApprovalStatus::DRAFT,
                    'current_approval_request_id' => null,
                ]);
                $this->logHistory($contract, 'cancelled', null, 'approval', $user, 'Pengajuan dibatalkan');
            }

            return $request->fresh(['steps']);
        });
    }

    /**
     * Antrean pengajuan yang menjadi tugas persetujuan user saat ini.
     */
    public function pendingFor(User $user): \Illuminate\Support\Collection
    {
        return ApprovalRequest::with(['subject', 'requestor', 'workflow'])
            ->where('status', 'in_review')
            ->get()
            ->filter(fn (ApprovalRequest $req) => $this->isApprover($req, $this->currentPendingStep($req), $user));
    }

    /**
     * Apakah user boleh bertindak pada step saat ini.
     */
    public function isApprover(ApprovalRequest $request, ?ApprovalStep $step, User $user): bool
    {
        if (! $step || $step->status !== 'pending') {
            return false;
        }

        if ((int) $request->requested_by === (int) $user->id) {
            return false; // anti self-approval
        }

        if ($user->isAdmin()) {
            return true; // admin override
        }

        if ($step->approver_user_id) {
            return (int) $step->approver_user_id === (int) $user->id;
        }

        if ($step->approver_type === 'department') {
            return $user->organization_unit_id && (int) $step->approver_value === (int) $user->organization_unit_id;
        }

        return false;
    }

    /**
     * Finalisasi: semua step setuju → kontrak efektif aktif.
     */
    protected function finalizeApproved(ApprovalRequest $request, User $user, string $note): void
    {
        $request->update([
            'status' => 'approved',
            'decided_at' => now(),
        ]);

        $contract = $request->subject;

        $contract->update([
            'approval_status' => ApprovalStatus::APPROVED,
            'status' => 'aktif',
            'current_approval_request_id' => null,
        ]);

        $this->logHistory($contract, 'approved', null, 'approval', $user,
            'Kontrak disetujui dan menjadi aktif'.($note ? " ($note)" : ''));
    }

    /**
     * Langkah pending untuk request (urut step_order).
     */
    protected function pendingSteps(ApprovalRequest $request): \Illuminate\Support\Collection
    {
        return $request->steps()->where('status', 'pending')->orderBy('step_order')->get();
    }

    protected function currentPendingStep(ApprovalRequest $request): ?ApprovalStep
    {
        return $request->steps()
            ->where('status', 'pending')
            ->orderBy('step_order')
            ->first();
    }

    /**
     * Resolve approver (user id) berdasarkan tipe step.
     */
    protected function resolveApproverUserId(WorkflowStep $step, User $requestor): ?int
    {
        switch ($step->approver_type) {
            case 'user':
                $user = User::find($step->approver_value);
                if (! $user) {
                    throw new WorkflowException("Approver user tidak ditemukan pada step: {$step->name}.");
                }

                return (int) $user->id;

            case 'role':
                $user = User::whereHas('role', fn ($q) => $q->where('key', $step->approver_value))
                    ->first();
                if (! $user) {
                    throw new WorkflowException("Tidak ada user berrole '{$step->approver_value}' pada step: {$step->name}.");
                }

                return (int) $user->id;

            case 'department_head':
                $unit = \App\Models\OrganizationUnit::find($step->approver_value);
                $head = $unit?->head;
                if (! $head) {
                    throw new WorkflowException("Kepala departemen belum ditentukan pada step: {$step->name}.");
                }

                return (int) $head->id;

            case 'department':
                // Anggota departemen: dipilih saat runtime (approver_user_id null).
                $unit = \App\Models\OrganizationUnit::find($step->approver_value);
                if (! $unit) {
                    throw new WorkflowException("Departemen tidak ditemukan pada step: {$step->name}.");
                }

                return null;

            default:
                throw new WorkflowException("Tipe approver tidak dikenal: {$step->approver_type}");
        }
    }

    /**
     * Evaluasi kondisi skip_if (JSON) terhadap data kontrak.
     * Contoh: {"field":"is_high_profile","op":"!=","value":true}
     */
    protected function shouldSkip(WorkflowStep $step, RuangLingkup $contract, User $requestor): bool
    {
        $skipIf = $step->skip_if;

        if (empty($skipIf) || ! isset($skipIf['field'])) {
            return false;
        }

        $actual = $contract->{$skipIf['field']} ?? null;
        $expected = $skipIf['value'] ?? null;
        $op = $skipIf['op'] ?? '==';

        if (is_bool($expected)) {
            $actual = (bool) $actual;
        } elseif (is_int($expected)) {
            $actual = $actual !== null ? (int) $actual : null;
        }

        return match ($op) {
            '==' => $actual == $expected,
            '!=' => $actual != $expected,
            '>' => $actual > $expected,
            '<' => $actual < $expected,
            default => false,
        };
    }

    protected function logHistory(RuangLingkup $contract, string $action, $oldStatus, $newStatus,
        ?User $user, string $notes): void
    {
        $contract->contractHistory()->create([
            'user_id' => $user?->id,
            'action_type' => $action,
            'old_end_date' => null,
            'new_end_date' => $contract->tanggal_berakhir,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'metadata' => [
                'actor' => $user?->name,
                'actor_id' => $user?->id,
            ],
        ]);
    }
}