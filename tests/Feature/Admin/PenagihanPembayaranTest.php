<?php

namespace Tests\Feature\Admin;

use App\Models\PenagihanPembayaran;
use App\Models\RuangLingkup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenagihanPembayaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.penagihan-pembayaran.index'));

        $response->assertStatus(200);
    }

    public function test_store_creates_record()
    {
        $this->loginAsAdmin();
        $rl = RuangLingkup::factory()->create();

        $response = $this->post(route('admin.penagihan-pembayaran.store'), [
            'ruang_lingkup_id' => $rl->id,
            'jangka_waktu_hari' => 30,
            'tipe_hari' => 'Hari',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('penagihan_pembayaran', [
            'ruang_lingkup_id' => $rl->id,
            'jangka_waktu_hari' => 30,
        ]);
    }

    public function test_update_changes_record()
    {
        $this->loginAsAdmin();
        $pp = PenagihanPembayaran::factory()->create();

        $response = $this->put(route('admin.penagihan-pembayaran.update', $pp), [
            'ruang_lingkup_id' => $pp->ruang_lingkup_id,
            'jangka_waktu_hari' => 60,
            'tipe_hari' => 'Kalender',
        ]);

        $this->assertDatabaseHas('penagihan_pembayaran', [
            'id' => $pp->id,
            'jangka_waktu_hari' => 60,
        ]);
    }

    public function test_destroy_deletes_record()
    {
        $this->loginAsAdmin();
        $pp = PenagihanPembayaran::factory()->create();

        $response = $this->delete(route('admin.penagihan-pembayaran.destroy', $pp));

        $this->assertModelMissing($pp);
    }
}
