<?php

namespace Tests;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected const ADMIN_PERMISSIONS = [
        'dashboard', 'kerjasama', 'approval',
        'workflow', 'departemen', 'master', 'reminder',
        'users', 'roles', 'pengaturan', 'laporan', 'notifikasi',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('config:clear');
    }

    /**
     * Buat (atau ambil) permission lalu lampirkan ke role — murni data DB.
     */
    protected function grantRolePermissions(Role $role, array $permissionNames): Role
    {
        foreach ($permissionNames as $name) {
            $permission = Permission::firstOrCreate(['name' => $name]);
            $role->permissions()->syncWithoutDetaching($permission->id);
        }

        return $role->fresh('permissions');
    }

    protected function createRoleWithPermissions(string $key, array $permissionNames): Role
    {
        $role = Role::factory()->create(['key' => $key, 'name' => ucfirst($key)]);

        return $this->grantRolePermissions($role, $permissionNames);
    }

    protected function createUserWithRole(string $roleKey, array $permissionNames = []): User
    {
        $role = Role::factory()->create(['key' => $roleKey, 'name' => ucfirst($roleKey)]);
        if (! empty($permissionNames)) {
            $this->grantRolePermissions($role, $permissionNames);
        }

        return User::factory()->create(['role_id' => $role->id]);
    }

    protected function createAdminUser(array $overrides = []): User
    {
        $user = User::factory()->admin()->create($overrides);
        $this->grantRolePermissions($user->role, self::ADMIN_PERMISSIONS);

        return $user;
    }

    protected function createNonAdminUser(array $overrides = []): User
    {
        $user = User::factory()->nonAdmin()->create($overrides);
        $this->grantRolePermissions($user->role, [
            'dashboard', 'kerjasama_read', 'approval', 'notifikasi', 'laporan',
        ]);

        return $user;
    }

    protected function loginAsAdmin(): User
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        return $user;
    }

    protected function loginAsNonAdmin(): User
    {
        $user = $this->createNonAdminUser();
        $this->actingAs($user);

        return $user;
    }
}