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
}