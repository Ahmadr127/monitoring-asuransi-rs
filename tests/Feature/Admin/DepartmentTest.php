<?php

namespace Tests\Feature\Admin;

use App\Models\OrganizationType;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200_for_admin(): void
    {
        $this->loginAsAdmin();

        $this->get(route('admin.departments.index'))->assertStatus(200);
    }

    public function test_store_type_creates_type(): void
    {
        $this->loginAsAdmin();

        $this->post(route('admin.departments.types.store'), [
            'name' => 'departemen',
            'display_name' => 'Departemen',
            'level' => 3,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('organization_types', ['name' => 'departemen']);
    }

    public function test_store_department_creates_unit_with_head(): void
    {
        $this->loginAsAdmin();
        $type = OrganizationType::factory()->create();
        $head = User::factory()->create();

        $this->post(route('admin.departments.store'), [
            'name' => 'Legal',
            'code' => 'LEGAL',
            'type_id' => $type->id,
            'head_id' => $head->id,
            'is_active' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('organization_units', [
            'code' => 'LEGAL',
            'head_id' => $head->id,
        ]);
    }

    public function test_update_department_changes_head(): void
    {
        $this->loginAsAdmin();
        $unit = OrganizationUnit::factory()->create();
        $newHead = User::factory()->create();

        $this->put(route('admin.departments.update', $unit), [
            'name' => $unit->name,
            'code' => $unit->code,
            'type_id' => $unit->type_id,
            'head_id' => $newHead->id,
            'is_active' => 1,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('organization_units', ['id' => $unit->id, 'head_id' => $newHead->id]);
    }

    public function test_destroy_deletes_unit(): void
    {
        $this->loginAsAdmin();
        $unit = OrganizationUnit::factory()->create();

        $this->delete(route('admin.departments.destroy', $unit))->assertRedirect()->assertSessionHas('success');

        $this->assertModelMissing($unit);
    }

    public function test_department_with_members_cannot_be_deleted(): void
    {
        $this->loginAsAdmin();
        $unit = OrganizationUnit::factory()->create();
        User::factory()->create(['organization_unit_id' => $unit->id]);

        $this->delete(route('admin.departments.destroy', $unit))->assertSessionHas('error');

        $this->assertDatabaseHas('organization_units', ['id' => $unit->id]);
    }

    public function test_non_admin_cannot_access_departments(): void
    {
        $this->loginAsNonAdmin();

        $this->get(route('admin.departments.index'))->assertStatus(403);
    }

    public function test_create_page_returns_200(): void
    {
        $this->loginAsAdmin();
        OrganizationType::factory()->create();

        $this->get(route('admin.departments.create'))->assertStatus(200);
    }

    public function test_show_page_returns_200(): void
    {
        $this->loginAsAdmin();
        $unit = OrganizationUnit::factory()->create();
        User::factory()->create(['organization_unit_id' => $unit->id]);

        $this->get(route('admin.departments.show', $unit))->assertStatus(200);
    }

    public function test_edit_page_returns_200(): void
    {
        $this->loginAsAdmin();
        $unit = OrganizationUnit::factory()->create();

        $this->get(route('admin.departments.edit', $unit))->assertStatus(200);
    }

    public function test_types_page_returns_200(): void
    {
        $this->loginAsAdmin();
        OrganizationType::factory()->create();

        $this->get(route('admin.departments.types.index'))->assertStatus(200);
    }

    public function test_add_member_assigns_user_to_unit(): void
    {
        $this->loginAsAdmin();
        $unit = OrganizationUnit::factory()->create();
        $user = User::factory()->create();

        $this->post(route('admin.departments.add-member', $unit), ['user_id' => $user->id])
            ->assertRedirect(route('admin.departments.show', $unit))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'organization_unit_id' => $unit->id]);
    }

    public function test_remove_member_clears_unit(): void
    {
        $this->loginAsAdmin();
        $unit = OrganizationUnit::factory()->create();
        $user = User::factory()->create(['organization_unit_id' => $unit->id]);

        $this->delete(route('admin.departments.remove-member', [$unit, $user]))
            ->assertRedirect(route('admin.departments.show', $unit));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'organization_unit_id' => null]);
    }

    public function test_update_head_changes_unit_head(): void
    {
        $this->loginAsAdmin();
        $unit = OrganizationUnit::factory()->create();
        $newHead = User::factory()->create();

        $this->patch(route('admin.departments.update-head', $unit), ['head_id' => $newHead->id])
            ->assertRedirect(route('admin.departments.show', $unit));

        $this->assertDatabaseHas('organization_units', ['id' => $unit->id, 'head_id' => $newHead->id]);
    }
}