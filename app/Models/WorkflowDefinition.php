<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('step_order');
    }

    public function activeSteps()
    {
        return $this->hasMany(WorkflowStep::class)->where('is_active', true)->orderBy('step_order');
    }

    public function requests()
    {
        return $this->hasMany(ApprovalRequest::class);
    }
}