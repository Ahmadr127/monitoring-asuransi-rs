<?php

namespace Tests\Feature\Admin;

use App\Models\BadanUsaha;
use App\Models\JenisLayanan;
use App\Models\Obat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    // --- Badan Usaha ---

    public function test_badan_usaha_index()
    {
        $this->loginAsAdmin();
        BadanUsaha::factory()->count(3)->create();

        $response = $this->get('/admin/master-badan-usaha');

        $response->assertStatus(200);
    }

    public function test_badan_usaha_store()
    {
        $this->loginAsAdmin();

        $response = $this->post('/admin/master-badan-usaha', [
            'nama_bu' => 'PT Test Baru',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('master_badan_usaha', ['nama_bu' => 'PT TEST BARU']);
    }

    public function test_badan_usaha_update()
    {
        $this->loginAsAdmin();
        $bu = BadanUsaha::factory()->create();

        $response = $this->put("/admin/master-badan-usaha/{$bu->id}", [
            'nama_bu' => 'PT Updated',
        ]);

        $this->assertDatabaseHas('master_badan_usaha', ['id' => $bu->id, 'nama_bu' => 'PT UPDATED']);
    }

    public function test_badan_usaha_destroy()
    {
        $this->loginAsAdmin();
        $bu = BadanUsaha::factory()->create();

        $response = $this->delete("/admin/master-badan-usaha/{$bu->id}");

        $this->assertModelMissing($bu);
    }

    // --- Jenis Layanan ---

    public function test_jenis_layanan_index()
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin/master-jenis-layanan');

        $response->assertStatus(200);
    }

    public function test_jenis_layanan_store()
    {
        $this->loginAsAdmin();

        $response = $this->post('/admin/master-jenis-layanan', [
            'nama' => 'Laboratorium',
            'kode' => 'LAB',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('master_jenis_layanan', ['kode' => 'LAB']);
    }

    public function test_jenis_layanan_update()
    {
        $this->loginAsAdmin();
        $jl = JenisLayanan::factory()->create();

        $response = $this->put("/admin/master-jenis-layanan/{$jl->id}", [
            'nama' => 'Updated Layanan',
            'kode' => 'updated',
        ]);

        $this->assertDatabaseHas('master_jenis_layanan', ['id' => $jl->id, 'nama' => 'UPDATED LAYANAN']);
    }

    public function test_jenis_layanan_destroy()
    {
        $this->loginAsAdmin();
        $jl = JenisLayanan::factory()->create();

        $response = $this->delete("/admin/master-jenis-layanan/{$jl->id}");

        $this->assertModelMissing($jl);
    }

    // --- Obat ---

    public function test_obat_index()
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin/master-obat');

        $response->assertStatus(200);
    }

    public function test_obat_store()
    {
        $this->loginAsAdmin();

        $response = $this->post('/admin/master-obat', [
            'nama' => 'Paracetamol',
            'kategori' => 'Generik',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('master_obat', ['nama' => 'PARACETAMOL']);
    }

    public function test_obat_update()
    {
        $this->loginAsAdmin();
        $obat = Obat::factory()->create();

        $response = $this->put("/admin/master-obat/{$obat->id}", [
            'nama' => 'Updated Obat',
            'kategori' => 'Paten',
        ]);

        $this->assertDatabaseHas('master_obat', ['id' => $obat->id, 'nama' => 'UPDATED OBAT']);
    }

    public function test_obat_destroy()
    {
        $this->loginAsAdmin();
        $obat = Obat::factory()->create();

        $response = $this->delete("/admin/master-obat/{$obat->id}");

        $this->assertModelMissing($obat);
    }
}
