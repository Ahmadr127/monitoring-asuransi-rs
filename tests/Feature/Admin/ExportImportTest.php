<?php

namespace Tests\Feature\Admin;

use App\Models\BadanUsaha;
use App\Models\RuangLingkup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExportImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_xlsx()
    {
        $this->loginAsAdmin();
        RuangLingkup::factory()->count(3)->create();

        $response = $this->get(route('admin.export.xlsx'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_csv()
    {
        $this->loginAsAdmin();
        RuangLingkup::factory()->count(3)->create();

        $response = $this->get(route('admin.export.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_import_form_returns_200()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.export.import-form'));

        $response->assertStatus(200);
    }

    public function test_download_template()
    {
        $this->loginAsAdmin();

        $response = $this->get(route('admin.export.template'));

        $response->assertStatus(200);
    }

    public function test_import_preview_shows_merge_for_existing_contract()
    {
        $this->loginAsAdmin();

        $bu1 = BadanUsaha::factory()->create(['nama_bu' => 'BU LAMA']);
        $contract = RuangLingkup::factory()->create([
            'nama_asuransi' => 'ASURANSI TEST',
            'penjamin_category' => 'TPA',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2026-12-31',
        ]);
        $contract->badanUsahaPivot()->create(['badan_usaha_id' => $bu1->id]);

        $tempPath = $this->createImportXlsx([
            ['ASURANSI TEST', '', '', 'TPA', '(BU BARU)', '', '', '', '', '', '', '', '', 'kalender', '2026-01-01', '2026-12-31', 'aktif'],
        ]);

        $file = new UploadedFile($tempPath, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('admin.export.import.preview'), ['file' => $file]);

        $response->assertStatus(200);
        $response->assertSee('Digabung');
        $response->assertSee('BU BARU');

        unlink($tempPath);
    }

    public function test_import_merge_adds_new_badan_usaha()
    {
        $this->loginAsAdmin();

        $bu1 = BadanUsaha::factory()->create(['nama_bu' => 'BU LAMA']);
        $contract = RuangLingkup::factory()->create([
            'nama_asuransi' => 'ASURANSI TEST',
            'penjamin_category' => 'TPA',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2026-12-31',
        ]);
        $contract->badanUsahaPivot()->create(['badan_usaha_id' => $bu1->id]);

        $tempPath = $this->createImportXlsx([
            ['ASURANSI TEST', '', '', 'TPA', '(BU BARU)', '', '', '', '', '', '', '', '', 'kalender', '2026-01-01', '2026-12-31', 'aktif'],
        ]);

        $file = new UploadedFile($tempPath, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->post(route('admin.export.import.preview'), ['file' => $file]);

        $response = $this->post(route('admin.export.import.confirm'), ['overwrite' => '0']);

        $response->assertSessionMissing('error');
        $response->assertSessionMissing('warning');

        $this->assertStringContainsString('digabung', session('success'));

        $contract->refresh();
        $this->assertCount(2, $contract->badanUsahaPivot);

        $buBaru = BadanUsaha::where('nama_bu', 'BU BARU')->first();
        $this->assertNotNull($buBaru);
        $this->assertTrue($contract->badanUsahaPivot->pluck('badan_usaha_id')->contains($buBaru->id));

        unlink($tempPath);
    }

    public function test_import_merge_skips_existing_badan_usaha()
    {
        $this->loginAsAdmin();

        $bu1 = BadanUsaha::factory()->create(['nama_bu' => 'BU SAMA']);
        $contract = RuangLingkup::factory()->create([
            'nama_asuransi' => 'ASURANSI TEST',
            'penjamin_category' => 'TPA',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2026-12-31',
        ]);
        $contract->badanUsahaPivot()->create(['badan_usaha_id' => $bu1->id]);

        // Import the same BU again — should skip (not duplicate)
        $tempPath = $this->createImportXlsx([
            ['ASURANSI TEST', '', '', 'TPA', '(BU SAMA)', '', '', '', '', '', '', '', '', 'kalender', '2026-01-01', '2026-12-31', 'aktif'],
        ]);

        $file = new UploadedFile($tempPath, 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->post(route('admin.export.import.preview'), ['file' => $file]);

        // Import the same BU — should not error and should not duplicate BU
        $response = $this->post(route('admin.export.import.confirm'), ['overwrite' => '0']);

        $response->assertSessionMissing('error');

        $contract->refresh();
        $this->assertCount(1, $contract->badanUsahaPivot);
        $this->assertEquals($bu1->id, $contract->badanUsahaPivot->first()->badan_usaha_id);

        unlink($tempPath);
    }

    private function createImportXlsx(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [
            'Nama Asuransi', 'Alias/Versi Tera', 'High Profile', 'TPA/PAYOR',
            'Badan Usaha', 'Nama Layanan', 'Kode Layanan', 'Benefit', 'Cakupan', 'Obat',
            'Deskripsi', 'Diskon (%)', 'Jangka Waktu (Hari)', 'Tipe Hari',
            'Tanggal Mulai', 'Tanggal Berakhir', 'Status',
        ];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $writer = new Xlsx($spreadsheet);
        $tempPath = tempnam(sys_get_temp_dir(), 'import_test_') . '.xlsx';
        $writer->save($tempPath);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $tempPath;
    }
}
