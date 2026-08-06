<?php

namespace Tests\Feature\Console;

use App\Mail\ContractReminderMail;
use App\Models\ReminderRecipient;
use App\Models\RuangLingkup;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContractCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_expired_command()
    {
        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);
        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDay(),
        ]);

        $this->artisan('contracts:update-expired')
            ->assertSuccessful();

        $this->assertEquals(1, RuangLingkup::where('status', 'tidak_aktif')->count());
    }

    public function test_generate_notifications_command()
    {
        $this->loginAsAdmin();

        $this->artisan('contracts:generate-notifications')
            ->assertSuccessful();
    }

    public function test_generate_notifications_creates_expiring_notification()
    {
        $admin = $this->loginAsAdmin();

        $contract = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(5),
        ]);

        $this->artisan('contracts:generate-notifications')
            ->assertSuccessful();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $admin->id,
            'type' => 'warning',
            'title' => 'Kontrak Akan Berakhir',
        ]);
    }

    public function test_generate_notifications_creates_new_contract_notification()
    {
        $admin = $this->loginAsAdmin();

        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addYear(),
            'created_at' => now()->subHour(),
        ]);

        $this->artisan('contracts:generate-notifications')
            ->assertSuccessful();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $admin->id,
            'type' => 'success',
            'title' => 'Kontrak Baru Ditambahkan',
        ]);
    }

    public function test_generate_notifications_does_not_duplicate()
    {
        $admin = $this->loginAsAdmin();

        $contract = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(5),
        ]);

        $this->artisan('contracts:generate-notifications')
            ->assertSuccessful();

        $this->artisan('contracts:generate-notifications')
            ->assertSuccessful();

        $count = UserNotification::where('user_id', $admin->id)
            ->where('title', 'Kontrak Akan Berakhir')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_send_reminders_command_sends_for_h_0_milestone()
    {
        ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        $contract = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(3),
        ]);

        Mail::fake();

        $this->artisan('app:send-contract-reminders')
            ->assertSuccessful();

        Mail::assertQueued(ContractReminderMail::class, function ($mail) use ($contract) {
            return collect($mail->expiring)->contains(fn ($c) => $c['id'] === $contract->id);
        });
    }

    public function test_send_reminders_command_sends_at_milestone()
    {
        ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        $contract = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(30),
        ]);

        Mail::fake();

        $this->artisan('app:send-contract-reminders')
            ->assertSuccessful();

        Mail::assertQueued(ContractReminderMail::class, function ($mail) use ($contract) {
            return collect($mail->expiring)->contains(fn ($c) => $c['id'] === $contract->id);
        });
    }

    public function test_send_reminders_command_skips_if_already_sent()
    {
        ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        $contract = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(30),
        ]);

        $contract->contractHistory()->create([
            'action_type' => 'reminder_sent',
            'old_status' => 'aktif',
            'new_status' => 'aktif',
            'notes' => 'Sent H-30 reminder',
        ]);

        Mail::fake();

        $this->artisan('app:send-contract-reminders')
            ->assertSuccessful();

        Mail::assertNothingOutgoing();
    }

    public function test_send_reminders_command_skips_expired_contracts()
    {
        ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);

        Mail::fake();

        $this->artisan('app:send-contract-reminders')
            ->assertSuccessful();

        Mail::assertNothingOutgoing();
    }

    public function test_send_reminders_command_catch_up_missed_milestone()
    {
        ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        $contract = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(25),
        ]);

        Mail::fake();

        $this->artisan('app:send-contract-reminders')
            ->assertSuccessful();

        Mail::assertQueued(ContractReminderMail::class, function ($mail) use ($contract) {
            return collect($mail->expiring)->contains(fn ($c) => $c['id'] === $contract->id);
        });
    }

    public function test_send_reminders_command_sends_to_both_categories()
    {
        ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);
        ReminderRecipient::factory()->create([
            'category' => 'manual',
            'is_active' => true,
        ]);

        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(7),
        ]);

        Mail::fake();

        $this->artisan('app:send-contract-reminders')
            ->assertSuccessful();

        Mail::assertQueued(ContractReminderMail::class, 2);
    }

    public function test_send_reminders_command_sends_expired_reminder()
    {
        ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        $contract = RuangLingkup::factory()->create([
            'status' => 'tidak_aktif',
            'tanggal_berakhir' => now()->subDays(5),
        ]);

        Mail::fake();

        $this->artisan('app:send-contract-reminders')
            ->assertSuccessful();

        Mail::assertQueued(ContractReminderMail::class, function ($mail) use ($contract) {
            return collect($mail->expired)->contains(fn ($c) => $c['id'] === $contract->id);
        });
    }

    public function test_send_reminders_command_skips_if_expired_already_sent()
    {
        ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        $contract = RuangLingkup::factory()->create([
            'status' => 'tidak_aktif',
            'tanggal_berakhir' => now()->subDays(5),
        ]);

        $contract->contractHistory()->create([
            'action_type' => 'expired_reminder_sent',
            'old_status' => 'tidak_aktif',
            'new_status' => 'tidak_aktif',
            'notes' => 'Sent expired reminder (digest)',
        ]);

        Mail::fake();

        $this->artisan('app:send-contract-reminders')
            ->assertSuccessful();

        Mail::assertNothingOutgoing();
    }
}
