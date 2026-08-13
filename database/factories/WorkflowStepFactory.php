<?php

namespace Database\Factories;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowStepFactory extends Factory
{
    protected $model = WorkflowStep::class;

    public function definition(): array
    {
        return [
            'workflow_definition_id' => WorkflowDefinition::factory(),
            'step_order' => 1,
            'name' => fake()->unique()->words(3, true),
            'approver_type' => 'role',
            'approver_value' => 'admin',
            'is_active' => true,
        ];
    }
}