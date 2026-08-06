<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_has_many_users()
    {
        $role = Role::factory()->create();
        User::factory()->count(3)->create(['role_id' => $role->id]);

        $this->assertCount(3, $role->users);
    }
}
