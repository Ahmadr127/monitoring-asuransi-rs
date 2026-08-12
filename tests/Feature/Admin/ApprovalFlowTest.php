<?php

namespace Tests\Feature\Admin;

use App\Models\OrganizationType;
use App\Models\OrganizationUnit;
use App\Models\Role;
use App\Models\RuangLingkup;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\ApprovalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    private array $setup = [];

    private function setUpFixture(): void
    {
        $adminRole = Role::create(['key' => 'admin', 'name' => 'Administrator']);
        $inputorRole = Role::create(['key' => 'inputor', 'name' => 'Penginput Kontrak']);
        $legalRole = Role::create(['key' => 'legal', 'name' => 'Legal']);
        $marketingRole = Role::create(['key' => 'marketing', 'name' => 'Marketing']);

        $this->grantRolePermissions($adminRole, self::ADMIN_PERMISSIONS);
        $this->grantRolePermissions($inputorRole, ['dashboard', 'kerjasama', 'kerjasama_read']);
        $this->grantRolePermissions($legalRole, ['dashboard', 'kerjasama_read', 'approval', 'notifikasi', 'laporan']);
        $this->grantRolePermissions($marketingRole, ['dashboard', 'kerjasama_read']);

        $this->setup['admin'] = User::factory()->create(['role_id' => $adminRole->id]);
        $penginput = User::factory()->create(['role_id' => $inputorRole->id]);
        $legalHead = User::factory()->create(['role_id' => $legalRole->id]);
        $this->setup['marketing'] = User::factory()->create(['role_id' => $marketingRole->id]);

        $dept = OrganizationUnit::factory()->create([
            'type_id' => OrganizationType::factory()->create()->id,
            'head_id' => $legalHead->id,
        ]);
        $penginput->update(['organization_unit_id' => $dept->id]);

        $wf = WorkflowDefinition::factory()->create(['is_active' => true, 'code' => 'kerjasama_baru']);
        $wf->steps()->create(['step_order' => 1, 'name' => 'Review Legal',
            'approver_type' => 'department_head', 'approver_value' => (string) $dept->id, 'can_reject' => true]);
        $wf->steps()->create(['step_order' => 2, 'name' => 'Persetujuan Admin',
            'approver_type' => 'user', 'approver_value' => (string) $this->setup['admin']->id, 'can_reject' => true]);

        $this->setup['penginput'] = $penginput;
        $this->setup['legalHead'] = $legalHead;
        $this->setup['dept'] = $dept;
        $this->setup['workflow'] = $wf;
    }

    private function makeDraftContract(): RuangLingkup
    {
        return RuangLingkup::factory()->create([
            'approval_status' => 'draft',
            'status' => 'tidak_aktif',
        ]);
    }

    public function test_penginput_submits_contract_and_it_enters_in_review(): void
    {
        $this->setUpFixture();
        $contract = $this->makeDraftContract();

        $this->actingAs($this->setup['penginput'])
            ->post(route('admin.ruang-lingkup.submit', $contract))
            ->assertRedirect(route('admin.approvals.index'))
            ->assertSessionHas('success');

        $contract->refresh();
        $this->assertEquals('in_review', $contract->approval_status->value);

        $request = ApprovalRequest::where('subject_id', $contract->id)->where('status', 'in_review')->first();
        $this->assertNotNull($request);
        $this->assertEquals('in_review', $request->status);

        $this->assertDatabaseHas('approval_steps', [
            'approval_request_id' => $request->id,
            'step_order' => 1,
            'approver_user_id' => $this->setup['legalHead']->id,
            'status' => 'pending',
        ]);
    }

    public function test_submit_without_workflow_shows_error_and_keeps_draft(): void
    {
        $this->setUpFixture();
        WorkflowDefinition::query()->delete();
        $contract = $this->makeDraftContract();

        $this->actingAs($this->setup['penginput'])
            ->post(route('admin.ruang-lingkup.submit', $contract))
            ->assertRedirect(route('admin.ruang-lingkup.show', $contract))
            ->assertSessionHas('error');

        $this->assertEquals('draft', $contract->refresh()->approval_status->value);
    }

    public function test_approved_contract_becomes_active_via_http(): void
    {
        $this->setUpFixture();
        $contract = $this->makeDraftContract();

        $this->actingAs($this->setup['penginput'])->post(route('admin.ruang-lingkup.submit', $contract));
        $request = ApprovalRequest::where('subject_id', $contract->id)->where('status', 'in_review')->first();

        $this->actingAs($this->setup['legalHead'])
            ->post(route('admin.approvals.approve', $request), ['note' => 'ok'])
            ->assertSessionHas('success');

        $this->actingAs($this->setup['admin'])
            ->post(route('admin.approvals.approve', $request));

        $contract->refresh();
        $request->refresh();

        $this->assertEquals('approved', $request->status);
        $this->assertEquals('approved', $contract->approval_status->value);
        $this->assertEquals('aktif', $contract->status);
        $this->assertNull($contract->current_approval_request_id);
    }

    public function test_reject_then_penginput_resubmits(): void
    {
        $this->setUpFixture();
        $contract = $this->makeDraftContract();

        $this->actingAs($this->setup['penginput'])->post(route('admin.ruang-lingkup.submit', $contract));
        $request = ApprovalRequest::where('subject_id', $contract->id)->where('status', 'in_review')->first();

        $this->actingAs($this->setup['legalHead'])
            ->post(route('admin.approvals.reject', $request), ['note' => 'Data belum lengkap'])
            ->assertSessionHas('success');

        $this->assertEquals('rejected', $request->refresh()->status);
        $this->assertEquals('rejected', $contract->refresh()->approval_status->value);

        // Re-submit
        $this->actingAs($this->setup['penginput'])
            ->post(route('admin.ruang-lingkup.submit', $contract));

        $this->assertEquals('in_review', $contract->refresh()->approval_status->value);
        $this->assertDatabaseHas('approval_requests', [
            'subject_id' => $contract->id,
            'status' => 'in_review',
        ]);
    }

    public function test_cancel_returns_contract_to_draft(): void
    {
        $this->setUpFixture();
        $contract = $this->makeDraftContract();

        $this->actingAs($this->setup['penginput'])->post(route('admin.ruang-lingkup.submit', $contract));
        $request = ApprovalRequest::where('subject_id', $contract->id)->where('status', 'in_review')->first();

        $this->actingAs($this->setup['penginput'])
            ->post(route('admin.approvals.cancel', $request))
            ->assertSessionHas('success');

        $this->assertEquals('cancelled', $request->refresh()->status);
        $this->assertEquals('draft', $contract->refresh()->approval_status->value);
    }

    public function test_non_approver_role_cannot_access_approval_queue(): void
    {
        $this->setUpFixture();

        $this->actingAs($this->setup['marketing'])
            ->get(route('admin.approvals.index'))
            ->assertStatus(403);

        $this->actingAs($this->setup['marketing'])
            ->post(route('admin.approvals.approve', ApprovalRequest::factory()->create()))
            ->assertStatus(403);
    }

    public function test_legal_head_sees_approval_queue(): void
    {
        $this->setUpFixture();
        $contract = $this->makeDraftContract();
        $this->actingAs($this->setup['penginput'])->post(route('admin.ruang-lingkup.submit', $contract));

        $this->actingAs($this->setup['legalHead'])
            ->get(route('admin.approvals.index'))
            ->assertStatus(200)
            ->assertSee($contract->nama_asuransi);
    }
}