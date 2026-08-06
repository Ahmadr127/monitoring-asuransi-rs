<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user()
    {
        $user = User::factory()->create();
        $notification = UserNotification::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($user->id, $notification->user->id);
    }

    public function test_unread_scope()
    {
        UserNotification::factory()->unread()->create();
        UserNotification::factory()->read()->create();

        $unread = UserNotification::unread()->get();

        $this->assertCount(1, $unread);
    }

    public function test_mark_as_read()
    {
        $notification = UserNotification::factory()->unread()->create();
        $notification->markAsRead();

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
