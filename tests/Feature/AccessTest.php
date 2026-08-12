<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\RuangLingkup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_mapping_comes_from_database(): void
    {
        $role = Role::factory()->create();
        $this->grantRolePermissions($role, ['laporan']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasPermission('laporan'));
        $this->assertFalse($user->hasPermission('obat'));
    }

    public function test_user_with_permission_can_access_feature(): void
    {
        $role = Role::factory()->create();
        $this->grantRolePermissions($role, ['laporan']);
        $user = User::factory()->create(['role_id' => $role->id]);
        RuangLingkup::factory()->create();

        $this->actingAs($user);
        $response = $this->get(route('admin.export.csv'));

        $response->assertStatus(200);
    }

    public function test_user_without_permission_gets_403(): void
    {
        $role = Role::factory()->create();
        $this->grantRolePermissions($role, ['dashboard', 'kerjasama_read']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user);
        $response = $this->get(route('admin.export.csv'));

        $response->assertStatus(403);
    }

    public function test_authorization_does_not_depend_on_role_name(): void
    {
        // Role "Auditor" — nama baru, tidak pernah ada di source code.
        $auditor = Role::factory()->create(['key' => 'auditor', 'name' => 'Auditor']);
        $this->grantRolePermissions($auditor, ['laporan']);
        $user = User::factory()->create(['role_id' => $auditor->id]);
        RuangLingkup::factory()->create();

        $this->actingAs($user);
        $this->assertTrue($user->hasPermission('laporan'));

        $this->get(route('admin.export.csv'))->assertStatus(200);
    }

    public function test_new_permission_can_be_created_via_database_without_source_change(): void
    {
        // Permission baru dibuat murni di database — tidak ada perubahan config/enum/access.
        $permission = Permission::create(['name' => 'monitoring']);

        $role = Role::factory()->create();
        $role->permissions()->attach($permission);
        $allowed = User::factory()->create(['role_id' => $role->id]);

        $blockedRole = Role::factory()->create();
        $this->grantRolePermissions($blockedRole, ['dashboard']);
        $blocked = User::factory()->create(['role_id' => $blockedRole->id]);

        Route::get('/test-perm/monitoring', fn () => response('ok'))
            ->middleware('web', 'permission:monitoring');

        $this->actingAs($allowed);
        $this->assertTrue($allowed->hasPermission('monitoring'));
        $this->get('/test-perm/monitoring')->assertStatus(200);

        $this->actingAs($blocked);
        $this->assertFalse($blocked->hasPermission('monitoring'));
        $this->get('/test-perm/monitoring')->assertStatus(403);
    }

    public function test_is_admin_is_derived_from_permissions_not_role_name(): void
    {
        // User dengan role "staff" (bukan admin) tapi diberi permission admin-level.
        $role = Role::factory()->create(['key' => 'staff', 'name' => 'Staff']);
        $this->grantRolePermissions($role, ['users', 'roles', 'dashboard', 'kerjasama']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->role->key === 'admin');
        $this->assertTrue($user->isAdmin());

        // Sebaliknya, role bernama "admin" tanpa permission tidak dianggap admin.
        $fakeAdminRole = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $fakeAdmin = User::factory()->create(['role_id' => $fakeAdminRole->id]);
        $this->assertFalse($fakeAdmin->isAdmin());
    }

    public function test_permission_assignment_can_be_extended_for_existing_role(): void
    {
        $role = Role::factory()->create();
        $this->grantRolePermissions($role, ['dashboard']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->hasPermission('laporan'));

        // Tambah permission via database → user langsung dapat akses.
        $this->grantRolePermissions($role, ['laporan']);

        $this->assertTrue($user->fresh()->hasPermission('laporan'));
    }

    public function test_sidebar_authorization_uses_database_permission(): void
    {
        $this->loginAsAdmin();
        $admin = auth()->user();

        $this->assertTrue($admin->hasPermission('workflow'));
        $this->assertTrue($admin->hasPermission('departemen'));
        $this->assertTrue($admin->hasPermission('approval'));

        $this->get(route('admin.dashboard'))->assertStatus(200);
    }
}