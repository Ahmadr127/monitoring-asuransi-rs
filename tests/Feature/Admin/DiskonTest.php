<?php

namespace Tests\Feature\Admin;

use App\Models\Diskon;
use App\Models\RuangLingkup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiskonTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.diskon.index'));

        $response->assertStatus(200);
    }

    public function test_store_creates_record()
    {
        $this->loginAsAdmin();
        $rl = RuangLingkup::factory()->create();

        $response = $this->post(route('admin.diskon.store'), [
            'ruang_lingkup_id' => $rl->id,
            'persentase_diskon' => 15,
            'keterangan' => 'Diskon test',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('diskon', [
            'ruang_lingkup_id' => $rl->id,
            'persentase_diskon' => 15,
        ]);
    }

    public function test_update_changes_record()
    {
        $this->loginAsAdmin();
        $diskon = Diskon::factory()->create();

        $response = $this->put(route('admin.diskon.update', $diskon), [
            'ruang_lingkup_id' => $diskon->ruang_lingkup_id,
            'persentase_diskon' => 25,
            'keterangan' => 'Updated',
        ]);

        $this->assertDatabaseHas('diskon', [
            'id' => $diskon->id,
            'persentase_diskon' => 25,
        ]);
    }

    public function test_destroy_deletes_record()
    {
        $this->loginAsAdmin();
        $diskon = Diskon::factory()->create();

        $response = $this->delete(route('admin.diskon.destroy', $diskon));

        $this->assertModelMissing($diskon);
    }
}
