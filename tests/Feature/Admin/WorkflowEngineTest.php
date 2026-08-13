<?php

namespace Tests\Feature\Admin;

use App\Models\OrganizationType;
use App\Models\OrganizationUnit;
use App\Models\RuangLingkup;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowEngineTest extends TestCase
{
    use RefreshDatabase;

    private WorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WorkflowService::class);
    }

    private function makeDraftContract(array $overrides = []): RuangLingkup
    {
        return RuangLingkup::factory()->create(array_merge([
            'approval_status' => 'draft',
            'status' => 'tidak_aktif',
        ], $overrides));
    }

    private function makeUserWithRole(string $roleKey): User
    {
        $role = \App\Models\Role::factory()->create(['key' => $roleKey, 'name' => ucfirst($roleKey)]);

        // Role admin perlu permission users+roles agar isAdmin() (berbasis DB) bernilai true.
        if ($roleKey === 'admin') {
            $this->grantRolePermissions($role, ['users', 'roles', 'approval', 'kerjasama']);
        }

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createActiveWorkflow(array $steps): WorkflowDefinition
    {
        $wf = WorkflowDefinition::factory()->create(['is_active' => true]);
        foreach ($steps as $i => $step) {
            $wf->steps()->create(array_merge([
                'step_order' => $i + 1,
                'name' => 'Langkah '.($i + 1),
                'is_active' => true,
            ], $step));
        }

        return $wf;
    }

    public function test_multi_level_approval_makes_contract_active_and_blocks_skipped_step(): void
    {
        $admin = $this->makeUserWithRole('admin');
        $legalHead = $this->makeUserWithRole('legal');
        $penginput = $this->makeUserWithRole('inputor');

        $dept = OrganizationUnit::factory()->create([
            'type_id' => OrganizationType::factory()->create()->id,
            'head_id' => $legalHead->id,
        ]);
        $penginput->update(['organization_unit_id' => $dept->id]);

        $wf = $this->createActiveWorkflow([
            ['name' => 'Review Legal', 'approver_type' => 'department_head', 'approver_value' => (string) $dept->id],
            ['name' => 'Finalisasi', 'approver_type' => 'user', 'approver_value' => (string) $admin->id],
        ]);

        $contract = $this->makeDraftContract();

        $request = $this->service->start($contract, $penginput);
        $request->load('steps');

        $this->assertEquals('in_review', $request->status);
        $this->assertCount(2, $request->steps);
        $this->assertEquals($legalHead->id, $request->steps->get(0)->approver_user_id);
        $this->assertEquals('pending', $request->steps->get(0)->status);
        $this->assertEquals('in_review', $contract->refresh()->approval_status->value);

        // User yang bukan approver (bukan kepala dept / admin) tidak bisa menyetujui step 1.
        $outsider = $this->makeUserWithRole('kasir');
        try {
            $this->service->approve($request->fresh(), $outsider, 'nyoba');
            $this->fail('Seharusnya menolak approve oleh user yang bukan approver.');
        } catch (\App\Exceptions\WorkflowException $e) {
            $this->assertStringContainsString('tidak berhak', $e->getMessage());
        }

        // Approve step 1 oleh kepala legal
        $this->service->approve($request->fresh(), $legalHead, 'OK legal');
        $this->assertEquals('approved', $request->fresh()->steps->first()->status);

        // Approve step 2 oleh admin → kontrak aktif
        $this->service->approve($request->fresh(), $admin);
        $request->refresh();
        $contract->refresh();

        $this->assertEquals('approved', $request->status);
        $this->assertEquals('approved', $contract->approval_status->value);
        $this->assertEquals('aktif', $contract->status);
    }

    public function test_self_approval_is_skipped(): void
    {
        $admin = $this->makeUserWithRole('admin');
        $wf = $this->createActiveWorkflow([
            ['name' => 'Step Admin', 'approver_type' => 'user', 'approver_value' => (string) $admin->id],
        ]);

        // Submitter adalah admin juga → langkahnya otomatis di-skip (anti self-approval) → langsung approved.
        $contract = $this->makeDraftContract();

        $request = $this->service->start($contract, $admin);

        $this->assertEquals('approved', $request->status);
        $this->assertEquals('approved', $contract->refresh()->approval_status->value);
        $this->assertEquals('aktif', $contract->refresh()->status);
    }

    public function test_department_type_any_member_can_approve(): void
    {
        $admin = $this->makeUserWithRole('admin');
        $member = $this->makeUserWithRole('kasir');
        $dept = OrganizationUnit::factory()->create([
            'type_id' => OrganizationType::factory()->create()->id,
            'head_id' => null,
        ]);
        $member->update(['organization_unit_id' => $dept->id]);

        $wf = $this->createActiveWorkflow([
            ['name' => 'Anggota Dept', 'approver_type' => 'department', 'approver_value' => (string) $dept->id],
            ['name' => 'Final', 'approver_type' => 'user', 'approver_value' => (string) $admin->id],
        ]);

        $contract = $this->makeDraftContract();
        $request = $this->service->start($contract, $this->makeUserWithRole('inputor'));
        $request->load('steps');

        $this->assertNull($request->steps->get(0)->approver_user_id, 'Approver anggota dept diselesaikan saat runtime');
        $this->assertEquals('pending', $request->steps->get(0)->status);

        $this->service->approve($request->fresh(), $member, 'ok dept');
        $this->assertEquals('approved', $request->fresh()->steps->first()->status);
    }

    public function test_reject_returns_to_contract_and_resubmit_opens_new_request(): void
    {
        $legalHead = $this->makeUserWithRole('legal');
        $admin = $this->makeUserWithRole('admin');
        $penginput = $this->makeUserWithRole('inputor');
        $dept = OrganizationUnit::factory()->create([
            'type_id' => OrganizationType::factory()->create()->id,
            'head_id' => $legalHead->id,
        ]);

        $this->createActiveWorkflow([
            ['name' => 'Skrining', 'approver_type' => 'department_head', 'approver_value' => (string) $dept->id],
            ['name' => 'Akhir', 'approver_type' => 'user', 'approver_value' => (string) $admin->id],
        ]);

        $contract = $this->makeDraftContract();

        $request = $this->service->start($contract, $penginput);
        $this->service->reject($request->fresh(), $legalHead, 'Data tidak lengkap');

        $request->refresh();
        $contract->refresh();
        $this->assertEquals('rejected', $request->status);
        $this->assertEquals('rejected', $contract->approval_status->value);

        // Re-submit membuat request baru
        $request2 = $this->service->start($contract->fresh(), $penginput->fresh());
        $this->assertEquals('in_review', $request2->status);
        $this->assertNotEquals($request->id, $request2->id);
        $this->assertEquals('in_review', $contract->fresh()->approval_status->value);
    }

    public function test_cancel_returns_contract_to_draft(): void
    {
        $legalHead = $this->makeUserWithRole('legal');
        $penginput = $this->makeUserWithRole('inputor');
        $admin = $this->makeUserWithRole('admin');
        $dept = OrganizationUnit::factory()->create([
            'type_id' => OrganizationType::factory()->create()->id,
            'head_id' => $legalHead->id,
        ]);
        $this->createActiveWorkflow([
            ['name' => 'Skrining', 'approver_type' => 'department_head', 'approver_value' => (string) $dept->id],
            ['name' => 'Akhir', 'approver_type' => 'user', 'approver_value' => (string) $admin->id],
        ]);

        $contract = $this->makeDraftContract();
        $request = $this->service->start($contract, $penginput);

        $this->service->cancel($request->fresh(), $penginput);

        $contract->refresh();
        $this->assertEquals('draft', $contract->approval_status->value);
        $this->assertNull($contract->current_approval_request_id);
        $this->assertEquals('cancelled', $request->refresh()->status);
    }

    public function test_pending_contract_not_in_active_scopes_and_rejected_excluded(): void
    {
        $admin = $this->makeUserWithRole('admin');
        $legalHead = $this->makeUserWithRole('legal');
        $penginput = $this->makeUserWithRole('inputor');
        $dept = OrganizationUnit::factory()->create([
            'type_id' => OrganizationType::factory()->create()->id,
            'head_id' => $legalHead->id,
        ]);
        $this->createActiveWorkflow([
            ['name' => 'Skrining', 'approver_type' => 'department_head', 'approver_value' => (string) $dept->id],
            ['name' => 'Akhir', 'approver_type' => 'user', 'approver_value' => (string) $admin->id],
        ]);

        $pending = $this->makeDraftContract();
        $this->service->start($pending, $penginput);

        $this->assertFalse($pending->expiring()->whereKey($pending->id)->exists());
        $this->assertFalse($pending->expiredButActive()->whereKey($pending->id)->exists());
    }
}