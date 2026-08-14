<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_definition_id',
        'subject_type',
        'subject_id',
        'requested_by',
        'current_step',
        'status',
        'submitted_at',
        'decided_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function steps()
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_order');
    }

    public function currentStepRecord()
    {
        return $this->hasOne(ApprovalStep::class)->where('step_order', $this->current_step)
            ->where('status', 'pending');
    }

    /**
     * Pengajuan yang menjadi tugas persetujuan user (approver langsung atau
     * anggota departemen). Eager-load + withCount disiapkan di sini.
     */
    public function scopePendingForMe($query, User $user)
    {
        return $query
            ->with(['subject', 'requestor', 'workflow', 'steps'])
            ->withCount('steps')
            ->whereHas('steps', function ($q) use ($user) {
                $q->where('approver_user_id', $user->id);

                if ($user->organization_unit_id) {
                    $q->orWhere(function ($q2) use ($user) {
                        $q2->where('approver_type', 'department')
                            ->where('approver_value', (string) $user->organization_unit_id);
                    });
                }
            })
            ->orderByDesc('created_at');
    }

    /**
     * Pengajuan yang dibuat oleh user.
     */
    public function scopeRequestedByMe($query, User $user)
    {
        return $query
            ->with(['subject', 'requestor', 'workflow', 'steps'])
            ->withCount('steps')
            ->where('requested_by', $user->id)
            ->orderByDesc('created_at');
    }
}