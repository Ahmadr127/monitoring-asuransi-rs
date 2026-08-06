<?php

namespace Tests\Feature\Admin;

use App\Mail\ContractReminderMail;
use App\Models\ReminderRecipient;
use App\Models\RuangLingkup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReminderRecipientTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.reminder-recipients.index'));

        $response->assertStatus(200);
    }

    public function test_store_creates_record()
    {
        $this->loginAsAdmin();

        $response = $this->post(route('admin.reminder-recipients.store'), [
            'email' => 'test@example.com',
            'name' => 'Test Recipient',
            'category' => 'internal',
            'purpose' => 'admin',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reminder_recipients', ['email' => 'test@example.com']);
    }

    public function test_update_changes_record()
    {
        $this->loginAsAdmin();
        $recipient = ReminderRecipient::factory()->create([
            'category' => 'internal',
        ]);

        $response = $this->put(route('admin.reminder-recipients.update', $recipient), [
            'name' => 'Updated Name',
            'email' => $recipient->email,
            'purpose' => 'legal',
        ]);

        $this->assertDatabaseHas('reminder_recipients', [
            'id' => $recipient->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_record()
    {
        $this->loginAsAdmin();
        $recipient = ReminderRecipient::factory()->create([
            'category' => 'internal',
        ]);

        $response = $this->delete(route('admin.reminder-recipients.destroy', $recipient));

        $this->assertModelMissing($recipient);
    }

    public function test_toggle_status()
    {
        $this->loginAsAdmin();
        $recipient = ReminderRecipient::factory()->create([
            'is_active' => true,
            'category' => 'internal',
        ]);

        $response = $this->patch(route('admin.reminder-recipients.toggle', $recipient));

        $this->assertFalse($recipient->fresh()->is_active);
    }

    public function test_send_manual_sends_email()
    {
        $this->loginAsAdmin();
        $recipient = ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        $contract = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(30),
        ]);

        Mail::fake();

        $response = $this->post(route('admin.reminder-recipients.send-manual', $recipient));

        $response->assertSessionHas('success');
        Mail::assertQueued(ContractReminderMail::class, function ($mail) use ($contract) {
            return collect($mail->expiring)->contains(fn ($c) => $c['id'] === $contract->id);
        });
    }

    public function test_send_manual_skips_expired_contracts()
    {
        $this->loginAsAdmin();
        $recipient = ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);

        Mail::fake();

        $response = $this->post(route('admin.reminder-recipients.send-manual', $recipient));

        $response->assertSessionHas('info');
        Mail::assertNothingOutgoing();
    }

    public function test_send_manual_resends_even_if_history_exists()
    {
        $this->loginAsAdmin();
        $recipient = ReminderRecipient::factory()->create([
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
            'notes' => 'Manual H-30 reminder',
        ]);

        Mail::fake();

        $response = $this->post(route('admin.reminder-recipients.send-manual', $recipient));

        $response->assertSessionHas('success');
        Mail::assertQueued(ContractReminderMail::class, function ($mail) use ($contract) {
            return collect($mail->expiring)->contains(fn ($c) => $c['id'] === $contract->id);
        });
    }

    public function test_send_manual_url_uses_route()
    {
        $this->loginAsAdmin();
        $recipient = ReminderRecipient::factory()->create([
            'category' => 'internal',
            'is_active' => true,
        ]);

        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(30),
        ]);

        Mail::fake();

        $this->post(route('admin.reminder-recipients.send-manual', $recipient));

        Mail::assertQueued(ContractReminderMail::class, function ($mail) {
            return count($mail->expiring) > 0;
        });
    }
}
