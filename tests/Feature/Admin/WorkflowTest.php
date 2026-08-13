<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200_for_admin(): void
    {
        $this->loginAsAdmin();

        $this->get(route('admin.workflows.index'))->assertStatus(200);
    }

    public function test_store_creates_workflow(): void
    {
        $this->loginAsAdmin();

        $this->post(route('admin.workflows.store'), [
            'code' => 'kerjasama_baru',
            'name' => 'Persetujuan Kontrak Baru',
            'is_active' => 1,
        ])->assertRedirect(route('admin.workflows.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('workflow_definitions', ['code' => 'kerjasama_baru']);
    }

    public function test_store_step_adds_dynamic_step(): void
    {
        $this->loginAsAdmin();
        $wf = WorkflowDefinition::factory()->create();

        $this->post(route('admin.workflows.steps.store', $wf), [
            'name' => 'Review Legal',
            'approver_type' => 'role',
            'approver_value' => 'legal',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('workflow_steps', [
            'workflow_definition_id' => $wf->id,
            'name' => 'Review Legal',
            'approver_type' => 'role',
            'approver_value' => 'legal',
        ]);
    }

    public function test_step_can_be_dynamically_removed(): void
    {
        $this->loginAsAdmin();
        $wf = WorkflowDefinition::factory()->create();
        $step = $wf->steps()->create([
            'step_order' => 1, 'name' => 'Review', 'approver_type' => 'role',
            'approver_value' => 'admin', 'is_active' => true,
        ]);

        $this->post(route('admin.workflows.steps.destroy', $step))->assertRedirect()->assertSessionHas('success');

        $this->assertModelMissing($step);
    }

    public function test_step_approver_choices_are_available_in_edit_page(): void
    {
        $this->loginAsAdmin();
        $wf = WorkflowDefinition::factory()->create();

        $this->get(route('admin.workflows.edit', $wf))
            ->assertStatus(200)
            ->assertSee('Berdasarkan Role')
            ->assertSee('User Langsung')
            ->assertSee('Kepala Departemen');
    }

    public function test_non_admin_cannot_access_workflow(): void
    {
        $this->loginAsNonAdmin();

        $this->get(route('admin.workflows.index'))->assertStatus(403);
    }
}