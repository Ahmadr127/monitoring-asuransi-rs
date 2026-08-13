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
        'requires_attachment',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_attachment' => 'boolean',
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