<?php

namespace Tests\Feature\User;

use App\Models\RuangLingkup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KerjasamaTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200()
    {
        $this->loginAsNonAdmin();

        $response = $this->get(route('user.kerjasama.index'));

        $response->assertStatus(200);
    }

    public function test_show_returns_200()
    {
        $this->loginAsNonAdmin();
        $rl = RuangLingkup::factory()->create();

        $response = $this->get(route('user.kerjasama.show', $rl));

        $response->assertStatus(200);
    }

    public function test_export_csv()
    {
        $this->loginAsNonAdmin();
        RuangLingkup::factory()->count(3)->create();

        $response = $this->get(route('user.kerjasama.export-csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_detail_page()
    {
        $this->loginAsNonAdmin();

        $response = $this->get(route('user.kerjasama.detail', 'all'));

        $response->assertStatus(200);
    }
}
