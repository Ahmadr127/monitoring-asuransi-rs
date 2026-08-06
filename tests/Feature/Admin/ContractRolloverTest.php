<?php

namespace Tests\Feature\Admin;

use App\Models\RuangLingkup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractRolloverTest extends TestCase
{
    use RefreshDatabase;

    public function test_rollover_creates_history()
    {
        $this->loginAsAdmin();
        $rl = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDay(),
        ]);

        $response = $this->post(route('admin.contracts.rollover', $rl), [
            'new_end_date' => now()->addYear()->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('contract_history', [
            'ruang_lingkup_id' => $rl->id,
            'action_type' => 'rollover',
        ]);
    }

    public function test_rollover_updates_end_date()
    {
        $this->loginAsAdmin();
        $rl = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDay(),
        ]);
        $newDate = now()->addYear()->format('Y-m-d');

        $this->post(route('admin.contracts.rollover', $rl), [
            'new_end_date' => $newDate,
        ]);

        $this->assertEquals($newDate, $rl->fresh()->tanggal_berakhir->format('Y-m-d'));
    }

    public function test_history_returns_200()
    {
        $this->loginAsAdmin();
        $rl = RuangLingkup::factory()->create();

        $response = $this->get(route('admin.contracts.history', $rl));

        $response->assertStatus(200);
    }

    public function test_update_expired_contracts()
    {
        $this->loginAsAdmin();

        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);

        $response = $this->post(route('admin.contracts.update-expired'));

        $response->assertStatus(200);
        $this->assertEquals(0, RuangLingkup::expiredButActive()->count());
    }

    public function test_status_check_returns_200()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.contracts.status-check'));

        $response->assertStatus(200);
    }
}
