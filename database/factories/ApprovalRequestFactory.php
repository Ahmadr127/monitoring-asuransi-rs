<?php

namespace Database\Factories;

use App\Models\ApprovalRequest;
use App\Models\RuangLingkup;
use App\Models\User;
use App\Models\WorkflowDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalRequestFactory extends Factory
{
    protected $model = ApprovalRequest::class;

    public function definition(): array
    {
        return [
            'workflow_definition_id' => WorkflowDefinition::factory(),
            'subject_type' => RuangLingkup::class,
            'subject_id' => RuangLingkup::factory(),
            'requested_by' => User::factory(),
            'current_step' => 1,
            'status' => 'in_review',
            'submitted_at' => now(),
        ];
    }
}