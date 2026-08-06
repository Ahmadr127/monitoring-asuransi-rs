<?php

namespace Tests\Feature\Admin;

use App\Models\RuangLingkup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_200()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_stats()
    {
        $this->loginAsAdmin();

        RuangLingkup::factory()->count(3)->create(['status' => 'aktif']);
        RuangLingkup::factory()->count(2)->create(['status' => 'tidak_aktif']);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('3');
        $response->assertSee('2');
    }

    public function test_dashboard_detail_aktif()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.kerjasama.detail', 'aktif'));

        $response->assertStatus(200);
    }

    public function test_dashboard_detail_all()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.kerjasama.detail', 'all'));

        $response->assertStatus(200);
    }

    public function test_dashboard_detail_expiring()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.kerjasama.detail', 'expiring'));

        $response->assertStatus(200);
    }

    public function test_dashboard_detail_inactive()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.kerjasama.detail', 'tidak_aktif'));

        $response->assertStatus(200);
    }

    public function test_dashboard_detail_invalid_redirects()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.kerjasama.detail', 'invalid'));

        $response->assertRedirect(route('admin.dashboard'));
    }
}
