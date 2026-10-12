<?php

namespace Tests\Unit;

use App\Models\BadanUsaha;
use App\Models\ContractHistory;
use App\Models\Diskon;
use App\Models\JenisLayanan;
use App\Models\Obat;
use App\Models\PenagihanPembayaran;
use App\Models\RuangLingkup;
use App\Models\RuangLingkupBadanUsaha;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuangLingkupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_fillable_attributes()
    {
        $rl = new RuangLingkup;

        $this->assertEquals([
            'nama_asuransi',
            'is_high_profile',
            'penjamin_category',
            'alias_versi_tera',
            'pic_nama',
            'pic_kontak',
            'pic_email',
            'pic_portal',
            'alamat_penagihan',
            'badan_usaha_id',
            'jenis_layanan',
            'benefit',
            'cakupan',
            'obat',
            'deskripsi',
            'tanggal_mulai',
            'tanggal_berakhir',
            'status',
            'approval_status',
            'created_by',
            'updated_by',
            'submitted_by',
            'current_approval_request_id',
        ], $rl->getFillable());
    }

    public function test_it_casts_dates_and_arrays()
    {
        $rl = RuangLingkup::factory()->create([
            'tanggal_mulai' => '2024-01-01',
            'tanggal_berakhir' => '2024-12-31',
            'jenis_layanan' => ['layanan-a', 'layanan-b'],
            'benefit' => ['benefit-a'],
            'cakupan' => ['cakupan-a'],
        ]);

        $this->assertInstanceOf(Carbon::class, $rl->tanggal_mulai);
        $this->assertInstanceOf(Carbon::class, $rl->tanggal_berakhir);
        $this->assertIsArray($rl->jenis_layanan);
        $this->assertIsArray($rl->benefit);
        $this->assertIsArray($rl->cakupan);
        $this->assertIsBool($rl->is_high_profile);
    }

    public function test_days_remaining_positive_when_future()
    {
        $rl = RuangLingkup::factory()->create([
            'tanggal_berakhir' => now()->addDays(30),
        ]);

        $this->assertEquals(30, $rl->days_remaining);
    }

    public function test_days_remaining_negative_when_past()
    {
        $rl = RuangLingkup::factory()->create([
            'tanggal_berakhir' => now()->subDays(5),
        ]);

        $this->assertEquals(-5, $rl->days_remaining);
    }

    public function test_days_remaining_zero_on_last_day()
    {
        $rl = RuangLingkup::factory()->create([
            'tanggal_berakhir' => now(),
        ]);

        $this->assertEquals(0, $rl->days_remaining);
    }

    public function test_is_expired_false_when_future()
    {
        $rl = RuangLingkup::factory()->create([
            'tanggal_berakhir' => now()->addDay(),
        ]);

        $this->assertFalse($rl->is_expired);
    }

    public function test_is_expired_true_on_last_day()
    {
        $rl = RuangLingkup::factory()->create([
            'tanggal_berakhir' => now(),
        ]);

        $this->assertTrue($rl->is_expired);
    }

    public function test_is_expired_true_when_past()
    {
        $rl = RuangLingkup::factory()->create([
            'tanggal_berakhir' => now()->subDay(),
        ]);

        $this->assertTrue($rl->is_expired);
    }

    public function test_status_badge_active()
    {
        $rl = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(60),
        ]);

        $badge = $rl->status_badge;

        $this->assertEquals('active', $badge['class']);
        $this->assertEquals('Aktif', $badge['text']);
        $this->assertEquals('success', $badge['color']);
    }

    public function test_status_badge_active_regardless_of_days()
    {
        $rl = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(3),
        ]);

        $badge = $rl->status_badge;

        $this->assertEquals('active', $badge['class']);
        $this->assertEquals('Aktif', $badge['text']);
        $this->assertEquals('success', $badge['color']);
    }

    public function test_status_badge_inactive_is_expired()
    {
        $rl = RuangLingkup::factory()->create([
            'status' => 'tidak_aktif',
            'tanggal_berakhir' => now()->addDays(60),
        ]);

        $badge = $rl->status_badge;

        $this->assertEquals('expired', $badge['class']);
        $this->assertEquals('Expired', $badge['text']);
        $this->assertEquals('danger', $badge['color']);
    }

    public function test_status_badge_expired()
    {
        $rl = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);

        $badge = $rl->status_badge;

        $this->assertEquals('expired', $badge['class']);
        $this->assertEquals('Expired', $badge['text']);
        $this->assertEquals('danger', $badge['color']);
    }

    public function test_update_expired_status_changes_inactive()
    {
        $rl = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);

        $result = $rl->updateExpiredStatus();

        $this->assertTrue($result);
        $this->assertEquals('tidak_aktif', $rl->fresh()->status);
    }

    public function test_update_expired_status_creates_history()
    {
        $rl = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);

        $rl->updateExpiredStatus();

        $this->assertDatabaseHas('contract_history', [
            'ruang_lingkup_id' => $rl->id,
            'action_type' => 'status_change',
            'old_status' => 'aktif',
            'new_status' => 'tidak_aktif',
        ]);
    }

    public function test_update_expired_status_noop_when_not_expired()
    {
        $rl = RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDay(),
        ]);

        $result = $rl->updateExpiredStatus();

        $this->assertFalse($result);
        $this->assertEquals('aktif', $rl->fresh()->status);
    }

    public function test_update_expired_status_noop_when_already_inactive()
    {
        $rl = RuangLingkup::factory()->create([
            'status' => 'tidak_aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);

        $result = $rl->updateExpiredStatus();

        $this->assertFalse($result);
    }

    public function test_scope_expiring_filters_correctly()
    {
        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(10),
        ]);
        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->addDays(60),
        ]);
        RuangLingkup::factory()->create([
            'status' => 'tidak_aktif',
            'tanggal_berakhir' => now()->addDays(10),
        ]);

        $expiring = RuangLingkup::expiring(30)->get();

        $this->assertCount(1, $expiring);
    }

    public function test_scope_expired_but_active()
    {
        RuangLingkup::factory()->create([
            'status' => 'aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);
        RuangLingkup::factory()->create([
            'status' => 'tidak_aktif',
            'tanggal_berakhir' => now()->subDay(),
        ]);

        $expiredActive = RuangLingkup::expiredButActive()->get();

        $this->assertCount(1, $expiredActive);
    }

    public function test_badan_usaha_relationship()
    {
        $badanUsaha = BadanUsaha::factory()->create();
        $rl = RuangLingkup::factory()->create(['badan_usaha_id' => $badanUsaha->id]);

        $this->assertInstanceOf(BadanUsaha::class, $rl->badanUsaha);
        $this->assertEquals($badanUsaha->id, $rl->badanUsaha->id);
    }

    public function test_badan_usaha_pivot_relationship()
    {
        $rl = RuangLingkup::factory()->create();
        $pivot = RuangLingkupBadanUsaha::factory()->create(['ruang_lingkup_id' => $rl->id]);

        $this->assertInstanceOf(RuangLingkupBadanUsaha::class, $rl->badanUsahaPivot->first());
        $this->assertEquals($pivot->id, $rl->badanUsahaPivot->first()->id);
    }

    public function test_diskon_relationship()
    {
        $rl = RuangLingkup::factory()->create();
        $diskon = Diskon::factory()->create(['ruang_lingkup_id' => $rl->id]);

        $this->assertInstanceOf(Diskon::class, $rl->diskon->first());
        $this->assertEquals($diskon->id, $rl->diskon->first()->id);
    }

    public function test_penagihan_pembayaran_relationship()
    {
        $rl = RuangLingkup::factory()->create();
        $penagihan = PenagihanPembayaran::factory()->create(['ruang_lingkup_id' => $rl->id]);

        $this->assertInstanceOf(PenagihanPembayaran::class, $rl->penagihanPembayaran->first());
        $this->assertEquals($penagihan->id, $rl->penagihanPembayaran->first()->id);
    }

    public function test_contract_history_relationship()
    {
        $rl = RuangLingkup::factory()->create();
        $history = ContractHistory::factory()->create(['ruang_lingkup_id' => $rl->id]);

        $this->assertInstanceOf(ContractHistory::class, $rl->contractHistory->first());
        $this->assertEquals($history->id, $rl->contractHistory->first()->id);
    }

    public function test_jenis_layanan_relasi_relationship()
    {
        $rl = RuangLingkup::factory()->create();
        $jenisLayanan = JenisLayanan::factory()->create();
        $rl->jenisLayananRelasi()->attach($jenisLayanan);

        $this->assertInstanceOf(JenisLayanan::class, $rl->jenisLayananRelasi->first());
        $this->assertEquals($jenisLayanan->id, $rl->jenisLayananRelasi->first()->id);
    }

    public function test_obat_relasi_relationship()
    {
        $rl = RuangLingkup::factory()->create();
        $obat = Obat::factory()->create();
        $rl->obatRelasi()->attach($obat);

        $this->assertInstanceOf(Obat::class, $rl->obatRelasi->first());
        $this->assertEquals($obat->id, $rl->obatRelasi->first()->id);
    }
}
