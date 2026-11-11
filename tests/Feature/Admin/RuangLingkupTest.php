<?php

namespace Tests\Feature\Admin;

use App\Models\BadanUsaha;
use App\Models\JenisLayanan;
use App\Models\RuangLingkup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuangLingkupTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.ruang-lingkup.index'));

        $response->assertStatus(200);
    }

    public function test_create_returns_200()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.ruang-lingkup.create'));

        $response->assertStatus(200);
    }

    public function test_store_creates_record()
    {
        $this->loginAsAdmin();
        $badanUsaha = BadanUsaha::factory()->create();
        $jenisLayanan = JenisLayanan::factory()->create();

        $response = $this->post(route('admin.ruang-lingkup.store'), [
            'nama_asuransi' => 'Test Asuransi',
            'penjamin_category' => 'TPA',
            'bu_groups' => [
                [
                    'badan_usaha_id' => $badanUsaha->id,
                    'jenis_layanan_ids' => [$jenisLayanan->id],
                ],
            ],
            'tanggal_mulai' => '2024-01-01',
            'tanggal_berakhir' => '2024-12-31',
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.ruang-lingkup.index'));
        $this->assertDatabaseHas('ruang_lingkup', [
            'nama_asuransi' => 'TEST ASURANSI',
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $this->loginAsAdmin();

        $response = $this->post(route('admin.ruang-lingkup.store'), []);

        $response->assertSessionHasErrors(['nama_asuransi', 'bu_groups', 'status']);
    }

    public function test_show_returns_200()
    {
        $this->loginAsAdmin();
        $rl = RuangLingkup::factory()->create();

        $response = $this->get(route('admin.ruang-lingkup.show', $rl));

        $response->assertStatus(200);
        $response->assertSee(\Illuminate\Support\Str::upper($rl->nama_asuransi));
    }

    public function test_edit_returns_200()
    {
        $this->loginAsAdmin();
        $rl = RuangLingkup::factory()->create();

        $response = $this->get(route('admin.ruang-lingkup.edit', $rl));

        $response->assertStatus(200);
    }

    public function test_update_changes_record()
    {
        $this->loginAsAdmin();
        $badanUsaha = BadanUsaha::factory()->create();
        $jenisLayanan = JenisLayanan::factory()->create();
        $rl = RuangLingkup::factory()->create();

        $response = $this->put(route('admin.ruang-lingkup.update', $rl), [
            'nama_asuransi' => 'Updated Asuransi',
            'penjamin_category' => 'PAYOR',
            'bu_groups' => [
                [
                    'badan_usaha_id' => $badanUsaha->id,
                    'jenis_layanan_ids' => [$jenisLayanan->id],
                ],
            ],
            'tanggal_mulai' => '2024-01-01',
            'tanggal_berakhir' => '2024-12-31',
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.ruang-lingkup.index'));
        $this->assertDatabaseHas('ruang_lingkup', [
            'id' => $rl->id,
            'nama_asuransi' => 'UPDATED ASURANSI',
        ]);
    }

    public function test_destroy_deletes_record()
    {
        $this->loginAsAdmin();
        $rl = RuangLingkup::factory()->create();

        $response = $this->delete(route('admin.ruang-lingkup.destroy', $rl));

        $response->assertRedirect(route('admin.ruang-lingkup.index'));
        $this->assertModelMissing($rl);
    }

    public function test_master_data_returns_json()
    {
        $this->loginAsAdmin();
        $badanUsaha = BadanUsaha::factory()->create();

        $response = $this->get(route('admin.ruang-lingkup.master-data'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['badan_usaha']);
    }

    public function test_non_admin_cannot_access()
    {
        $this->loginAsNonAdmin();

        $response = $this->get(route('admin.ruang-lingkup.index'));

        $response->assertStatus(403);
    }
}
