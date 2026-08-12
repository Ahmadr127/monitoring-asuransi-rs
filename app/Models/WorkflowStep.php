<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_definition_id',
        'step_order',
        'name',
        'approver_type',
        'approver_value',
        'can_reject',
        'is_active',
        'skip_if',
    ];

    protected $casts = [
        'can_reject' => 'boolean',
        'is_active' => 'boolean',
        'skip_if' => 'array',
    ];

    public function workflow()
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class, 'workflow_step_id');
    }
}