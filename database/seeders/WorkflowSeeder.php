<?php

namespace Database\Seeders;

use App\Models\OrganizationUnit;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $legalDept = OrganizationUnit::where('code', 'LEGAL')->first();
        $direksiDept = OrganizationUnit::where('code', 'DIREKSI')->first();

        $definitions = [
            [
                'code' => 'contract_approval',
                'name' => 'Persetujuan Kontrak',
                'description' => 'Alur persetujuan kontrak kerjasama asuransi secara berjenjang.',
                'is_active' => true,
                'steps' => [
                    ['step_order' => 1, 'name' => 'Review Legal', 'approver_type' => 'role', 'approver_value' => 'legal'],
                    ['step_order' => 2, 'name' => 'Review Marketing', 'approver_type' => 'role', 'approver_value' => 'marketing'],
                    [
                        'step_order' => 3,
                        'name' => 'Persetujuan Direksi',
                        'approver_type' => $direksiDept ? 'department_head' : 'role',
                        'approver_value' => $direksiDept?->id ?? 'admin',
                    ],
                ],
            ],
        ];

        $totalSteps = 0;

        foreach ($definitions as $data) {
            $steps = $data['steps'];
            unset($data['steps']);

            $definition = WorkflowDefinition::firstOrCreate(['code' => $data['code']], $data);

            foreach ($steps as $step) {
                WorkflowStep::updateOrCreate(
                    [
                        'workflow_definition_id' => $definition->id,
                        'step_order' => $step['step_order'],
                    ],
                    [
                        'name' => $step['name'],
                        'approver_type' => $step['approver_type'],
                        'approver_value' => $step['approver_value'],
                        'is_active' => true,
                    ]
                );
                $totalSteps++;
            }
        }

        $this->command->info('✅ Workflow seeded: '.WorkflowDefinition::count().' definition, '.$totalSteps.' steps');
    }
}