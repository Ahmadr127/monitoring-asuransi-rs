<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_index()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    public function test_user_store()
    {
        $this->loginAsAdmin();
        $role = Role::factory()->create();

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $role->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['username' => 'testuser']);
    }

    public function test_user_update()
    {
        $this->loginAsAdmin();
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $response = $this->put(route('admin.users.update', $user), [
            'name' => 'Updated Name',
            'username' => $user->username,
            'email' => $user->email,
            'role_id' => $role->id,
        ]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    public function test_user_destroy()
    {
        $this->loginAsAdmin();
        $user = User::factory()->create();

        $response = $this->delete(route('admin.users.destroy', $user));

        $this->assertModelMissing($user);
    }

    public function test_role_index()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.roles.index'));

        $response->assertStatus(200);
    }

    public function test_role_store()
    {
        $this->loginAsAdmin();

        $response = $this->post(route('admin.roles.store'), [
            'name' => 'New Role',
            'key' => 'new_role',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('roles', ['key' => 'new_role']);
    }

    public function test_role_update()
    {
        $this->loginAsAdmin();
        $role = Role::factory()->create();

        $response = $this->put(route('admin.roles.update', $role), [
            'name' => 'Updated Role',
            'key' => $role->key,
        ]);

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'Updated Role']);
    }

    public function test_role_destroy()
    {
        $this->loginAsAdmin();
        $role = Role::factory()->create();

        $response = $this->delete(route('admin.roles.destroy', $role));

        $this->assertModelMissing($role);
    }
}
