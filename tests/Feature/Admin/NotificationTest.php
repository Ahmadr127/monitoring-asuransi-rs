<?php

namespace Tests\Feature\Admin;

use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200()
    {
        $user = $this->loginAsAdmin();
        UserNotification::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->get(route('admin.notifications.index'));

        $response->assertStatus(200);
    }

    public function test_unread_count()
    {
        $user = $this->loginAsAdmin();
        UserNotification::factory()->count(2)->unread()->create(['user_id' => $user->id]);
        UserNotification::factory()->read()->create(['user_id' => $user->id]);

        $response = $this->get(route('admin.notifications.unread-count'));

        $response->assertStatus(200);
        $response->assertJson(['count' => 2]);
    }

    public function test_mark_as_read()
    {
        $user = $this->loginAsAdmin();
        $notification = UserNotification::factory()->unread()->create(['user_id' => $user->id]);

        $response = $this->get(route('admin.notifications.read', $notification));

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read()
    {
        $user = $this->loginAsAdmin();
        UserNotification::factory()->count(3)->unread()->create(['user_id' => $user->id]);

        $response = $this->post(route('admin.notifications.mark-all-read'));

        $this->assertEquals(0, UserNotification::unread()->where('user_id', $user->id)->count());
    }
}
