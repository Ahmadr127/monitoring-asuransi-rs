<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_returns_true_for_admin_role()
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_non_admin_role()
    {
        $user = User::factory()->nonAdmin()->create();

        $this->assertFalse($user->isAdmin());
    }

    public function test_user_belongs_to_role()
    {
        $role = Role::factory()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals($role->id, $user->role->id);
    }

    public function test_user_has_notifications()
    {
        $user = User::factory()->create();
        $notification = UserNotification::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(UserNotification::class, $user->notifications->first());
        $this->assertEquals($notification->id, $user->notifications->first()->id);
    }

    public function test_unread_notifications_scope()
    {
        $user = User::factory()->create();
        UserNotification::factory()->unread()->create(['user_id' => $user->id]);
        UserNotification::factory()->read()->create(['user_id' => $user->id]);

        $this->assertCount(1, $user->unreadNotifications);
    }
}
