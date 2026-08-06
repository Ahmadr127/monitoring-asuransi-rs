<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadanUsaha;
use App\Models\JenisLayanan;
use App\Models\Obat;
use App\Models\RuangLingkup;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportImportController extends Controller
{
    public function exportXlsx()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Nama Asuransi', 'Alias/Versi Tera', 'High Profile', 'TPA/PAYOR',
            'Badan Usaha', 'Nama Layanan', 'Kode Layanan', 'Benefit', 'Cakupan', 'Obat',
            'Deskripsi',
            'Diskon (%)', 'Jangka Waktu (Hari)', 'Tipe Hari',
            'Tanggal Mulai', 'Tanggal Berakhir', 'Sisa Waktu (Hari)', 'Status',
        ];

        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007774']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:R1')->applyFromArray($headerStyle);

        $data = $this->getExportData();
        $sheet->fromArray($data, null, 'A2');

        $lastRow = count($data) + 1;
        $sheet->getStyle("A1:R{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = 'export-kerjasama-'.now()->format('Y-m-d-His').'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportCsv()
    {
        $headers = [
            'Nama Asuransi', 'Alias/Versi Tera', 'High Profile', 'TPA/PAYOR',
            'Badan Usaha', 'Nama Layanan', 'Kode Layanan', 'Benefit', 'Cakupan', 'Obat',
            'Deskripsi',
            'Diskon (%)', 'Jangka Waktu (Hari)', 'Tipe Hari',
            'Tanggal Mulai', 'Tanggal Berakhir', 'Sisa Waktu (Hari)', 'Status',
        ];

        $data = $this->getExportData();

        $callback = function () use ($headers, $data) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $headers);

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        $filename = 'export-kerjasama-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf()
    {
        $contracts = RuangLingkup::with([
            'diskon', 'penagihanPembayaran', 'badanUsaha', 'badanUsahaPivot.badanUsaha',
            'jenisLayananRelasi', 'obatRelasi',
        ])->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('admin.export.pdf', compact('contracts'));

        return $pdf->download('export-kerjasama-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function exportSinglePdf(RuangLingkup $rl)
    {
        $rl->load([
            'diskon', 'penagihanPembayaran', 'badanUsaha', 'badanUsahaPivot.badanUsaha',
            'jenisLayananRelasi', 'obatRelasi', 'contractHistory',
        ]);

        $daysRemaining = $rl->days_remaining;
        $isExpired = $rl->is_expired;

        $pdf = Pdf::loadView('admin.export.single-pdf', compact('rl', 'daysRemaining', 'isExpired'));

        return $pdf->download('kontrak-'.$rl->nama_asuransi.'-'.now()->format('Y-m-d').'.pdf');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Nama Asuransi', 'Alias/Versi Tera', 'High Profile', 'TPA/PAYOR',
            'Badan Usaha', 'Nama Layanan', 'Kode Layanan', 'Benefit', 'Cakupan', 'Obat',
            'Deskripsi',
            'Diskon (%)', 'Jangka Waktu (Hari)', 'Tipe Hari',
            'Tanggal Mulai', 'Tanggal Berakhir', 'Status',
        ];

        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007774']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:Q1')->applyFromArray($headerStyle);

        $examples = [
            [
                'Asuransi ABC', 'v1.0', 'Ya', 'TPA',
                '(Badan Usaha 1)(Badan Usaha 2)',
                '(Rawat Inap)(Rawat Jalan, IGD)',
                '(RI)(RJ, IGD)',
                '(Konsultasi, Tindakan)(MCU)',
                '(Karyawan)(Keluarga)',
                '(Antibiotik)(Vitamin)',
                '(Catatan BU1)(Catatan BU2)',
                '10', '30', 'kalender',
                '2026-01-01', '2026-12-31', 'aktif',
            ],
        ];

        $sheet->fromArray($examples, null, 'A2');

        $exampleStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDFA']],
        ];
        $sheet->getStyle('A2:Q2')->applyFromArray($exampleStyle);

        $sheet->getStyle('A2:Q2')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('A1:Q2')->getFont()->setName('Calibri');

        // Note row
        $noteStyle = [
            'font' => ['italic' => true, 'color' => ['rgb' => '64748B'], 'size' => 9],
        ];
        $sheet->setCellValue('A3', 'Note: 1 baris = 1 kontrak. Multi BU: (BU 1)(BU 2). Detail per BU urut sesuai (). Pisahkan item dengan koma.');
        $sheet->mergeCells('A3:Q3');
        $sheet->getStyle('A3:Q3')->applyFromArray($noteStyle);
        $sheet->getRowDimension(3)->setRowHeight(24);

        $sheet->setCellValue('A4', 'Status: isi "aktif" atau "tidak_aktif". Kosongkan = default aktif. Tidak case-sensitive.');
        $sheet->mergeCells('A4:Q4');
        $sheet->getStyle('A4:Q4')->applyFromArray($noteStyle);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Sheet 2: Petunjuk
        $guideSheet = $spreadsheet->createSheet();
        $guideSheet->setTitle('Petunjuk');
        $guideSheet->getColumnDimension('A')->setWidth(22);
        $guideSheet->getColumnDimension('B')->setWidth(50);

        $guideStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 13],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007774']],
        ];
        $guideSheet->setCellValue('A1', 'Petunjuk Pengisian Template');
        $guideSheet->mergeCells('A1:B1');
        $guideSheet->getStyle('A1:B1')->applyFromArray($guideStyle);

        $guideData = [
            ['Simbol *', 'Kolom wajib diisi'],
            ['', ''],
            ['FORMAT DASAR', '1 baris = 1 kontrak.'],
            ['', 'Multi BU: (BU 1)(BU 2). Urutan () = urutan BU.'],
            ['', 'Pisahkan item dengan koma: (item1, item2)(item3).'],
            ['', ''],
            ['Nama Asuransi *', 'Nama perusahaan asuransi/penjamin'],
            ['Alias/Versi Tera', 'Nama lain/alias kontrak (opsional)'],
            ['High Profile', 'Ya / Tidak'],
            ['TPA/PAYOR', 'TPA / Payor / TPA & PAYOR'],
            ['Badan Usaha', '(Nama BU 1)(Nama BU 2) — gunakan () untuk multi BU'],
            ['Nama Layanan', '(layanan BU1)(layanan BU2)'],
            ['Kode Layanan', '(kode BU1)(kode BU2)'],
            ['Benefit', '(benefit BU1)(benefit BU2)'],
            ['Cakupan', '(cakupan BU1)(cakupan BU2)'],
            ['Obat', '(obat BU1)(obat BU2)'],
            ['Deskripsi', '(catatan BU1)(catatan BU2)'],
            ['Diskon (%)', 'Angka, contoh: 10'],
            ['Jangka Waktu (Hari)', 'Angka, contoh: 30'],
            ['Tipe Hari', 'kalender / kerja'],
            ['Tanggal Mulai *', 'Format: YYYY-MM-DD (contoh: 2026-01-01)'],
            ['Tanggal Berakhir *', 'Format: YYYY-MM-DD (contoh: 2026-12-31)'],
            ['Status', 'aktif / tidak_aktif. Kosongkan = default aktif.'],
        ];

        $guideSheet->fromArray($guideData, null, 'A3');
        $guideSheet->getStyle('A3:B25')->getFont()->setName('Calibri')->setSize(11);
        $guideSheet->getStyle('A3:B25')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $guideSheet->getStyle('A3:B7')->getFont()->setBold(true);
        $guideSheet->getStyle('A3:B7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F0FDFA');

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $filename = 'template-import-kerjasama.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importForm()
    {
        return view('admin.export.import');
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        [$rows, $indexMap] = $this->parseImportFile($request->file('file'));

        if ($rows === null) {
            return back()->with('error', 'Tidak dapat mendeteksi kolom. Pastikan header Excel sesuai template.');
        }

        if (empty($rows)) {
            return back()->with('error', 'File tidak memiliki data.');
        }

        $previewRows = [];
        $summary = ['new' => 0, 'merge' => 0, 'error' => 0];

        foreach ($rows as $line => $row) {
            $excelRow = $line + 2;
            $validation = $this->validateImportRow($row, $indexMap);

            if ($validation['skip']) {
                continue;
            }

            if (! $validation['valid']) {
                $summary['error']++;
                $previewRows[] = [
                    'excel_row' => $excelRow,
                    'nama_asuransi' => $validation['nama'] ?: '-',
                    'badan_usaha' => $validation['badan_usaha'] ?: '-',
                    'tanggal_mulai' => $validation['tgl_mulai'],
                    'tanggal_berakhir' => $validation['tgl_berakhir'],
                    'status' => 'error',
                    'message' => implode('; ', $validation['errors']),
                ];
                continue;
            }

            $nama = Str::upper($validation['nama']);
            $penjaminCategory = $validation['penjamin_category'];
            $parsedMulai = $validation['parsed_mulai'];
            $parsedBerakhir = $validation['parsed_berakhir'];
            $badanUsaha = $validation['badan_usaha'];

            $existing = RuangLingkup::whereRaw('LOWER(nama_asuransi) = ?', [Str::lower($nama)])
                ->where(function ($q) use ($penjaminCategory) {
                    empty($penjaminCategory)
                        ? $q->whereNull('penjamin_category')->orWhere('penjamin_category', '')
                        : $q->where('penjamin_category', $penjaminCategory);
                })
                ->where('tanggal_mulai', $parsedMulai)
                ->where('tanggal_berakhir', $parsedBerakhir)
                ->orderBy('id')
                ->first(['id', 'status', 'updated_at']);

            if ($existing) {
                $summary['merge']++;
                $previewRows[] = [
                    'excel_row' => $excelRow,
                    'nama_asuransi' => $nama,
                    'badan_usaha' => $badanUsaha,
                    'tanggal_mulai' => $validation['tgl_mulai'],
                    'tanggal_berakhir' => $validation['tgl_berakhir'],
                    'status' => 'merge',
                    'message' => 'Akan digabung (BU baru)',
                    'existing_id' => $existing->id,
                ];
                continue;
            }

            $summary['new']++;
            $previewRows[] = [
                'excel_row' => $excelRow,
                'nama_asuransi' => $nama,
                'badan_usaha' => $badanUsaha,
                'tanggal_mulai' => $validation['tgl_mulai'],
                'tanggal_berakhir' => $validation['tgl_berakhir'],
                'status' => 'new',
                'message' => 'Data baru',
            ];
        }

        usort($previewRows, fn ($a, $b) =>
            (['error' => 0, 'merge' => 1, 'new' => 2][$a['status']] ?? 99) <=>
            (['error' => 0, 'merge' => 1, 'new' => 2][$b['status']] ?? 99)
        );

        $tempDir = Storage::disk('local')->path('import-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $basename = 'import_'.md5(uniqid()).'.xlsx';
        $tempFile = $tempDir.'/'.$basename;
        copy($request->file('file')->getPathname(), $tempFile);

        $request->session()->put('import_temp_file', $basename);

        return view('admin.export.import-preview', [
            'previewRows' => $previewRows,
            'summary' => $summary,
            'fileName' => $request->file('file')->getClientOriginalName(),
        ]);
    }

    public function confirmImport(Request $request)
    {
        $request->validate([
            'overwrite' => 'nullable|boolean',
        ]);

        $tempFileName = $request->session()->pull('import_temp_file');

        if (! $tempFileName || ! preg_match('/^import_[a-f0-9]{32}\.xlsx$/', $tempFileName)) {
            return redirect()->route('admin.export.import-form')
                ->with('error', 'Sesi import tidak valid. Silakan upload ulang.');
        }

        $tempDir = Storage::disk('local')->path('import-temp');
        $tempPath = $tempDir.'/'.$tempFileName;

        if (! file_exists($tempPath)) {
            return redirect()->route('admin.export.import-form')
                ->with('error', 'File sementara tidak ditemukan. Silakan upload ulang.');
        }

        try {
            [$rows, $indexMap] = $this->parseImportFile($tempPath);
        } catch (\Exception $e) {
            @unlink($tempPath);

            return redirect()->route('admin.export.import-form')
                ->with('error', 'Gagal membaca file: '.$e->getMessage());
        }

        if ($rows === null) {
            @unlink($tempPath);

            return redirect()->route('admin.export.import-form')
                ->with('error', 'Tidak dapat membaca file. Silakan upload ulang.');
        }

        $overwrite = $request->boolean('overwrite');

        DB::beginTransaction();

        try {
            $result = $this->processImportRows($rows, $indexMap, $overwrite);

            if (! empty($result['errors'])) {
                DB::rollBack();
                @unlink($tempPath);

                $result['imported'] = 0;
                $result['updated'] = 0;
                $result['merged'] = 0;

                return $this->handleImportResult($result);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            @unlink($tempPath);

            return redirect()->route('admin.export.import-form')
                ->with('error', 'Terjadi kesalahan saat import: '.$e->getMessage());
        }

        @unlink($tempPath);

        return $this->handleImportResult($result);
    }

    private function parseImportFile($file): array
    {
        $path = $file instanceof \Illuminate\Http\UploadedFile ? $file->getPathname() : $file;

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            return [[], []];
        }

        $excelHeaders = array_shift($rows);

        $autoMap = [
            'Nama Asuransi' => 'nama_asuransi',
            'Alias' => 'alias_versi_tera',
            'Versi Tera' => 'alias_versi_tera',
            'High Profile' => 'is_high_profile',
            'TPA' => 'penjamin_category',
            'Payor' => 'penjamin_category',
            'Badan Usaha' => 'badan_usaha_raw',
            'Nama Layanan' => 'jenis_layanan_raw',
            'Jenis Layanan' => 'jenis_layanan_raw',
            'Kode Layanan' => 'kode_layanan_raw',
            'Benefit' => 'benefit_raw',
            'Cakupan' => 'cakupan_raw',
            'Obat' => 'obat_raw',
            'Deskripsi' => 'deskripsi_raw',
            'Diskon' => 'diskon_persen',
            'Jangka Waktu' => 'jangka_waktu',
            'Tipe Hari' => 'tipe_hari',
            'Tanggal Mulai' => 'tanggal_mulai',
            'Tanggal Berakhir' => 'tanggal_berakhir',
            'Status' => 'status',
        ];

        $indexMap = [];
        foreach ($excelHeaders as $colIndex => $colName) {
            $colNameTrimmed = trim($colName);
            foreach ($autoMap as $key => $field) {
                if (stripos($colNameTrimmed, $key) !== false) {
                    $indexMap[$field] = $colIndex;
                    break;
                }
            }
        }

        if (empty($indexMap)) {
            return [null, null];
        }

        return [$rows, $indexMap];
    }
    private function validateImportRow(array $row, array $indexMap): array
    {
        $nama = $this->cellValue($row, $indexMap, 'nama_asuransi');
        $tglMulai = $this->cellValue($row, $indexMap, 'tanggal_mulai');
        $tglBerakhir = $this->cellValue($row, $indexMap, 'tanggal_berakhir');
        $statusRaw = $this->cellValue($row, $indexMap, 'status');
        $highRaw = $this->cellValue($row, $indexMap, 'is_high_profile');
        $penjaminCategory = $this->cellValue($row, $indexMap, 'penjamin_category') ?: '';
        $buRaw = $this->cellValue($row, $indexMap, 'badan_usaha_raw');
        $tipeHariRaw = $this->cellValue($row, $indexMap, 'tipe_hari');

        $allEmpty = true;
        foreach ($row as $cell) {
            if ($cell !== null && $cell !== '' && $cell !== '0000-00-00') {
                $allEmpty = false;
                break;
            }
        }

        if ($allEmpty) {
            return ['valid' => false, 'skip' => true];
        }

        $errors = [];

        if (empty($nama)) {
            $errors[] = 'Nama Asuransi wajib diisi.';
        }

        $parsedMulai = null;
        $parsedBerakhir = null;
        if (empty($tglMulai) || empty($tglBerakhir)) {
            $errors[] = 'Tanggal Mulai dan Tanggal Berakhir wajib diisi.';
        } else {
            try {
                $parsedMulai = Carbon::parse($tglMulai);
                $parsedBerakhir = Carbon::parse($tglBerakhir);
            } catch (\Exception $e) {
                $errors[] = 'Format tanggal tidak valid (gunakan YYYY-MM-DD).';
            }
        }

        $status = '';
        if (! empty($statusRaw)) {
            $normalized = strtolower(trim(str_replace([' ', '_', '-'], '_', $statusRaw)));
            if (in_array($normalized, ['aktif', 'active', '1', 'yes', 'true'])) {
                $status = 'aktif';
            } elseif (in_array($normalized, ['tidak_aktif', 'nonaktif', 'non_aktif', 'inactive', '0', 'no', 'false'])) {
                $status = 'tidak_aktif';
            } else {
                $errors[] = "Status harus 'aktif' atau 'tidak_aktif'.";
            }
        }

        if (! empty($highRaw)) {
            $normalizedHigh = strtolower(trim($highRaw));
            if (! in_array($normalizedHigh, ['ya', 'yes', '1', 'true', 'tidak', 'no', '0', 'false'])) {
                $errors[] = "High Profile harus 'Ya' atau 'Tidak'.";
            }
        }
        $isHighProfile = in_array(strtolower($this->cellValue($row, $indexMap, 'is_high_profile')), ['ya', 'yes', '1', 'true']);

        if (! empty($penjaminCategory)) {
            $normalizedPenjamin = strtolower(trim($penjaminCategory));
            if (! in_array($normalizedPenjamin, ['tpa', 'payor', 'tpa & payor'])) {
                $errors[] = "TPA/PAYOR harus 'TPA', 'Payor', atau 'TPA & PAYOR'.";
            }
        }

        if (! empty($tipeHariRaw)) {
            $normalizedTipe = strtolower(trim($tipeHariRaw));
            if (! in_array($normalizedTipe, ['kalender', 'kerja'])) {
                $errors[] = "Tipe Hari harus 'kalender' atau 'kerja'.";
            }
        }

        $buNames = $this->parseParentheses($buRaw);
        if (empty($buNames)) {
            $errors[] = 'Badan Usaha wajib diisi. Gunakan (Nama BU) sebagai pemisah.';
        }

        return [
            'valid' => empty($errors),
            'skip' => false,
            'errors' => $errors,
            'nama' => $nama,
            'penjamin_category' => $penjaminCategory,
            'tgl_mulai' => $tglMulai,
            'tgl_berakhir' => $tglBerakhir,
            'parsed_mulai' => $parsedMulai,
            'parsed_berakhir' => $parsedBerakhir,
            'status' => $status,
            'is_high_profile' => $isHighProfile,
            'tipe_hari' => $tipeHariRaw ?: 'kalender',
            'bu_names' => $buNames,
            'badan_usaha' => $buRaw,
        ];
    }

    private function processImportRows(array $rows, array $indexMap, bool $overwrite = false): array
    {
        $imported = 0;
        $updated = 0;
        $merged = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $line => $row) {
            if (! empty($errors)) {
                break;
            }

            $validation = $this->validateImportRow($row, $indexMap);

            if ($validation['skip']) {
                continue;
            }

            if (! $validation['valid']) {
                foreach ($validation['errors'] as $error) {
                    $errors[] = 'Baris '.($line + 2).': '.$error;
                }
                continue;
            }

            $nama = Str::upper($validation['nama']);
            $status = $validation['status'];
            $isHighProfile = $validation['is_high_profile'];
            $buNames = array_map(fn($n) => Str::upper($n), $validation['bu_names']);
            $penjaminCategory = $validation['penjamin_category'];
            $parsedMulai = $validation['parsed_mulai'];
            $parsedBerakhir = $validation['parsed_berakhir'];

            $layananGroups = $this->parseGroupedItems($this->cellValue($row, $indexMap, 'jenis_layanan_raw'));
            $kodeGroups = $this->parseGroupedItems($this->cellValue($row, $indexMap, 'kode_layanan_raw'));
            $benefitGroups = $this->parseGroupedItems($this->cellValue($row, $indexMap, 'benefit_raw'));
            $cakupanGroups = $this->parseGroupedItems($this->cellValue($row, $indexMap, 'cakupan_raw'));
            $obatGroups = $this->parseGroupedItems($this->cellValue($row, $indexMap, 'obat_raw'));
            $deskripsiGroups = $this->parseParentheses($this->cellValue($row, $indexMap, 'deskripsi_raw'));

            $existing = RuangLingkup::whereRaw('LOWER(nama_asuransi) = ?', [Str::lower($nama)])
                ->where(function ($q) use ($penjaminCategory) {
                    empty($penjaminCategory)
                        ? $q->whereNull('penjamin_category')->orWhere('penjamin_category', '')
                        : $q->where('penjamin_category', $penjaminCategory);
                })
                ->where('tanggal_mulai', $parsedMulai)
                ->where('tanggal_berakhir', $parsedBerakhir)
                ->orderBy('id')
                ->first();

            if ($existing) {
                if (! $overwrite) {
                    try {
                        $existingBuIds = $existing->badanUsahaPivot()->pluck('badan_usaha_id')->toArray();
                        $allLayananIds = $existing->jenisLayananRelasi()->pluck('master_jenis_layanan.id')->toArray();
                        $allObatIds = $existing->obatRelasi()->pluck('master_obat.id')->toArray();
                        $hasNewBu = false;

                        foreach ($buNames as $i => $buName) {
                            $bu = BadanUsaha::whereRaw('LOWER(nama_bu) = ?', [Str::lower($buName)])->first();
                            if (! $bu) {
                                $bu = BadanUsaha::create(['nama_bu' => $buName]);
                            }

                            if (in_array($bu->id, $existingBuIds)) {
                                continue;
                            }

                            $buLayananIds = [];
                            foreach ($layananGroups[$i] ?? [] as $j => $name) {
                                if (! empty($name)) {
                                    $kode = $kodeGroups[$i][$j] ?? null;
                                    $koded = $kode ? Str::upper($kode) : null;
                                    $ly = null;
                                    if ($koded) {
                                        $ly = JenisLayanan::whereRaw('LOWER(kode) = ?', [Str::lower($koded)])->first();
                                    }
                                    if (! $ly) {
                                        $ly = JenisLayanan::whereRaw('LOWER(nama) = ?', [Str::lower($name)])->first();
                                    }
                                    if (! $ly) {
                                        $ly = JenisLayanan::create([
                                            'nama' => Str::upper($name),
                                            'kode' => $koded ?: $this->generateKodeLayanan($name),
                                        ]);
                                    } elseif ($koded && ! $ly->kode) {
                                        $ly->update(['kode' => $koded]);
                                    }
                                    $buLayananIds[] = $ly->id;
                                }
                            }

                            $buObatIds = [];
                            foreach ($obatGroups[$i] ?? [] as $name) {
                                if (! empty($name)) {
                                    $ob = Obat::whereRaw('LOWER(nama) = ?', [Str::lower($name)])->first();
                                    if (! $ob) {
                                        $ob = Obat::create(['nama' => Str::upper($name)]);
                                    }
                                    $buObatIds[] = $ob->id;
                                }
                            }

                            $existing->badanUsahaPivot()->create([
                                'badan_usaha_id' => $bu->id,
                                'benefit' => $benefitGroups[$i] ?? [],
                                'cakupan' => $cakupanGroups[$i] ?? [],
                                'jenis_layanan_ids' => $buLayananIds,
                                'obat_ids' => $buObatIds,
                                'deskripsi' => $deskripsiGroups[$i] ?? '',
                            ]);

                            $allLayananIds = array_merge($allLayananIds, $buLayananIds);
                            $allObatIds = array_merge($allObatIds, $buObatIds);
                            $hasNewBu = true;
                        }

                        if ($hasNewBu) {
                            if (! empty($allLayananIds)) {
                                $existing->jenisLayananRelasi()->sync(array_unique($allLayananIds));
                            }
                            if (! empty($allObatIds)) {
                                $existing->obatRelasi()->sync(array_unique($allObatIds));
                            }

                            $existing->contractHistory()->create([
                                'action_type' => 'updated',
                                'old_status' => $existing->status,
                                'new_status' => $existing->status,
                                'notes' => 'Ditambah BU via import (merge)',
                            ]);

                            $merged++;
                        }
                    } catch (\Exception $e) {
                        $errors[] = 'Baris '.($line + 2)." (merge): {$e->getMessage()}";
                    }

                    continue;
                }

                try {
                    $oldStatus = $existing->status;
                    $newStatus = $status ?: 'aktif';

                    $existing->update([
                        'nama_asuransi' => $nama,
                        'alias_versi_tera' => $this->cellValue($row, $indexMap, 'alias_versi_tera'),
                        'is_high_profile' => $isHighProfile,
                        'penjamin_category' => $penjaminCategory,
                        'tanggal_mulai' => $parsedMulai,
                        'tanggal_berakhir' => $parsedBerakhir,
                        'status' => $newStatus,
                    ]);

                    $existing->badanUsahaPivot()->delete();
                    $existing->jenisLayananRelasi()->sync([]);
                    $existing->obatRelasi()->sync([]);

                    $allLayananIds = [];
                    $allObatIds = [];

                    foreach ($buNames as $i => $buName) {
                        $bu = BadanUsaha::whereRaw('LOWER(nama_bu) = ?', [Str::lower($buName)])->first();
                        if (! $bu) {
                            $bu = BadanUsaha::create(['nama_bu' => $buName]);
                        }

                        $buLayananIds = [];
                        foreach ($layananGroups[$i] ?? [] as $j => $name) {
                            if (! empty($name)) {
                                $kode = $kodeGroups[$i][$j] ?? null;
                                $koded = $kode ? Str::upper($kode) : null;
                                $ly = null;
                                if ($koded) {
                                    $ly = JenisLayanan::whereRaw('LOWER(kode) = ?', [Str::lower($koded)])->first();
                                }
                                if (! $ly) {
                                    $ly = JenisLayanan::whereRaw('LOWER(nama) = ?', [Str::lower($name)])->first();
                                }
                                if (! $ly) {
                                    $ly = JenisLayanan::create([
                                        'nama' => Str::upper($name),
                                        'kode' => $koded ?: $this->generateKodeLayanan($name),
                                    ]);
                                } elseif ($koded && ! $ly->kode) {
                                    $ly->update(['kode' => $koded]);
                                }
                                $buLayananIds[] = $ly->id;
                            }
                        }

                        $buObatIds = [];
                        foreach ($obatGroups[$i] ?? [] as $name) {
                            if (! empty($name)) {
                                $ob = Obat::whereRaw('LOWER(nama) = ?', [Str::lower($name)])->first();
                                if (! $ob) {
                                    $ob = Obat::create(['nama' => Str::upper($name)]);
                                }
                                $buObatIds[] = $ob->id;
                            }
                        }

                        $existing->badanUsahaPivot()->create([
                            'badan_usaha_id' => $bu->id,
                            'benefit' => $benefitGroups[$i] ?? [],
                            'cakupan' => $cakupanGroups[$i] ?? [],
                            'jenis_layanan_ids' => $buLayananIds,
                            'obat_ids' => $buObatIds,
                            'deskripsi' => $deskripsiGroups[$i] ?? '',
                        ]);

                        $allLayananIds = array_merge($allLayananIds, $buLayananIds);
                        $allObatIds = array_merge($allObatIds, $buObatIds);
                    }

                    if (! empty($allLayananIds)) {
                        $existing->jenisLayananRelasi()->sync(array_unique($allLayananIds));
                    }

                    if (! empty($allObatIds)) {
                        $existing->obatRelasi()->sync(array_unique($allObatIds));
                    }

                    $diskonPersen = $this->cellValue($row, $indexMap, 'diskon_persen');
                    $existing->diskon()->delete();
                    if (! empty($diskonPersen) && is_numeric($diskonPersen)) {
                        $existing->diskon()->create([
                            'persentase_diskon' => (float) $diskonPersen,
                        ]);
                    }

                    $jangkaWaktu = $this->cellValue($row, $indexMap, 'jangka_waktu');
                    $existing->penagihanPembayaran()->delete();
                    if (! empty($jangkaWaktu) && is_numeric($jangkaWaktu)) {
                        $existing->penagihanPembayaran()->create([
                            'jangka_waktu_hari' => (int) $jangkaWaktu,
                            'tipe_hari' => $validation['tipe_hari'],
                        ]);
                    }

                    $existing->contractHistory()->create([
                        'action_type' => 'updated',
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'notes' => 'Diupdate via import (timpa data)',
                    ]);

                    $updated++;
                } catch (\Exception $e) {
                    $errors[] = 'Baris '.($line + 2)." (timpa): {$e->getMessage()}";
                }

                continue;
            }

            try {
                $rl = RuangLingkup::create([
                    'nama_asuransi' => $nama,
                    'alias_versi_tera' => $this->cellValue($row, $indexMap, 'alias_versi_tera'),
                    'is_high_profile' => $isHighProfile,
                    'penjamin_category' => $penjaminCategory,
                    'badan_usaha_id' => null,
                    'jenis_layanan' => [],
                    'benefit' => [],
                    'cakupan' => [],
                    'obat' => [],
                    'tanggal_mulai' => $parsedMulai,
                    'tanggal_berakhir' => $parsedBerakhir,
                    'status' => $status ?: 'aktif',
                ]);

                $allLayananIds = [];
                $allObatIds = [];

                foreach ($buNames as $i => $buName) {
                    $bu = BadanUsaha::whereRaw('LOWER(nama_bu) = ?', [Str::lower($buName)])->first();
                    if (! $bu) {
                        $bu = BadanUsaha::create(['nama_bu' => $buName]);
                    }

                    $buLayananIds = [];
                    foreach ($layananGroups[$i] ?? [] as $j => $name) {
                        if (! empty($name)) {
                            $kode = $kodeGroups[$i][$j] ?? null;
                            $koded = $kode ? Str::upper($kode) : null;
                            $ly = null;
                            if ($koded) {
                                $ly = JenisLayanan::whereRaw('LOWER(kode) = ?', [Str::lower($koded)])->first();
                            }
                            if (! $ly) {
                                $ly = JenisLayanan::whereRaw('LOWER(nama) = ?', [Str::lower($name)])->first();
                            }
                            if (! $ly) {
                                $ly = JenisLayanan::create([
                                    'nama' => Str::upper($name),
                                    'kode' => $koded ?: $this->generateKodeLayanan($name),
                                ]);
                            } elseif ($koded && ! $ly->kode) {
                                $ly->update(['kode' => $koded]);
                            }
                            $buLayananIds[] = $ly->id;
                        }
                    }

                    $buObatIds = [];
                    foreach ($obatGroups[$i] ?? [] as $name) {
                        if (! empty($name)) {
                            $ob = Obat::whereRaw('LOWER(nama) = ?', [Str::lower($name)])->first();
                            if (! $ob) {
                                $ob = Obat::create(['nama' => Str::upper($name)]);
                            }
                            $buObatIds[] = $ob->id;
                        }
                    }

                    $rl->badanUsahaPivot()->create([
                        'badan_usaha_id' => $bu->id,
                        'benefit' => $benefitGroups[$i] ?? [],
                        'cakupan' => $cakupanGroups[$i] ?? [],
                        'jenis_layanan_ids' => $buLayananIds,
                        'obat_ids' => $buObatIds,
                        'deskripsi' => $deskripsiGroups[$i] ?? '',
                    ]);

                    $allLayananIds = array_merge($allLayananIds, $buLayananIds);
                    $allObatIds = array_merge($allObatIds, $buObatIds);
                }

                if (! empty($allLayananIds)) {
                    $rl->jenisLayananRelasi()->sync(array_unique($allLayananIds));
                }

                if (! empty($allObatIds)) {
                    $rl->obatRelasi()->sync(array_unique($allObatIds));
                }

                $diskonPersen = $this->cellValue($row, $indexMap, 'diskon_persen');
                if (! empty($diskonPersen) && is_numeric($diskonPersen)) {
                    $rl->diskon()->create([
                        'persentase_diskon' => (float) $diskonPersen,
                    ]);
                }

                $jangkaWaktu = $this->cellValue($row, $indexMap, 'jangka_waktu');
                if (! empty($jangkaWaktu) && is_numeric($jangkaWaktu)) {
                    $rl->penagihanPembayaran()->create([
                        'jangka_waktu_hari' => (int) $jangkaWaktu,
                        'tipe_hari' => $validation['tipe_hari'],
                    ]);
                }

                $rl->contractHistory()->create([
                    'action_type' => 'created',
                    'old_status' => null,
                    'new_status' => $rl->status,
                    'notes' => null,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = 'Baris '.($line + 2).": {$e->getMessage()}";
            }
        }

        return compact('imported', 'updated', 'merged', 'skipped', 'errors');
    }

    private function handleImportResult(array $result)
    {
        $imported = $result['imported'];
        $updated = $result['updated'];
        $merged = $result['merged'] ?? 0;
        $skipped = $result['skipped'] ?? 0;
        $errors = $result['errors'];

        if ($imported === 0 && $updated === 0 && $merged === 0 && $skipped === 0 && ! empty($errors)) {
            return redirect()->route('admin.export.import-form')
                ->with('error', 'Import gagal. '.count($errors).' baris error.')
                ->with('import_errors', $errors);
        }

        if ($imported === 0 && $updated === 0 && $merged === 0 && $skipped === 0) {
            return redirect()->route('admin.export.import-form')
                ->with('info', 'Tidak ada data baru atau perubahan.');
        }

        $parts = [];
        if ($imported > 0) {
            $parts[] = "{$imported} data baru";
        }
        if ($updated > 0) {
            $parts[] = "{$updated} data ditimpa";
        }
        if ($merged > 0) {
            $parts[] = "{$merged} data digabung (BU baru)";
        }
        $message = 'Berhasil: '.implode(', ', $parts).'.';

        if ($skipped > 0) {
            $message .= " {$skipped} data dilewati (duplikat).";
        }

        if (! empty($errors)) {
            $message .= ' '.count($errors).' baris gagal.';

            return redirect()->route('admin.ruang-lingkup.index')
                ->with('warning', $message)
                ->with('import_errors', $errors);
        }

        return redirect()->route('admin.ruang-lingkup.index')
            ->with('success', $message);
    }

    private function parseParentheses(string $value): array
    {
        preg_match_all('/\(([^)]*)\)/', $value, $matches);
        $items = array_map('trim', $matches[1]);

        return array_values(array_filter($items, fn ($v) => $v !== ''));
    }

    private function parseGroupedItems(string $value): array
    {
        $groups = $this->parseParentheses($value);

        return array_map(function ($group) {
            return array_values(array_filter(array_map('trim', explode(',', $group)), fn ($v) => $v !== ''));
        }, $groups);
    }

    private function cellValue($row, $indexMap, $key)
    {
        if (! isset($indexMap[$key]) || ! isset($row[$indexMap[$key]])) {
            return '';
        }

        $value = $row[$indexMap[$key]];

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $isDateKey = in_array($key, ['tanggal_mulai', 'tanggal_berakhir']);

        if ($isDateKey && is_numeric($value) && $value > 40000 && $value < 100000) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)->format('Y-m-d');
        }

        return trim((string) $value);
    }

    private function generateKodeLayanan(string $name): string
    {
        $base = Str::slug($name, '-');
        if (empty($base)) {
            $base = 'layanan';
        }

        $kode = $base;
        $suffix = 1;
        while (JenisLayanan::where('kode', $kode)->exists()) {
            $kode = $base.'-'.$suffix;
            $suffix++;
        }

        return $kode;
    }

    private function getExportData()
    {
        $contracts = RuangLingkup::with([
            'diskon', 'penagihanPembayaran', 'badanUsaha', 'badanUsahaPivot.badanUsaha',
            'jenisLayananRelasi', 'obatRelasi',
        ])->orderBy('created_at', 'desc')->get();

        $data = [];

        foreach ($contracts as $c) {
            $buNames = $c->badanUsahaPivot->map(fn ($p) => $p->badanUsaha?->nama_bu)
                ->filter()->values()->toArray();

            if (empty($buNames) && $c->badanUsaha) {
                $buNames = [$c->badanUsaha->nama_bu];
            }

            $data[] = [
                $c->nama_asuransi,
                $c->alias_versi_tera ?? '',
                $c->is_high_profile ? 'Ya' : 'Tidak',
                $c->penjamin_category ?? '',
                collect($buNames)->map(fn ($v) => "({$v})")->implode(''),
                $c->badanUsahaPivot->map(fn ($p) => '('.collect($p->jenis_layanan_ids)->map(fn ($id) => $c->jenisLayananRelasi->firstWhere('id', $id)?->nama)->filter()->implode(', ').')')->implode(''),
                $c->badanUsahaPivot->map(fn ($p) => '('.collect($p->jenis_layanan_ids)->map(fn ($id) => $c->jenisLayananRelasi->firstWhere('id', $id)?->kode)->filter()->implode(', ').')')->implode(''),
                $c->badanUsahaPivot->map(fn ($p) => '('.collect($p->benefit)->implode(', ').')')->implode(''),
                $c->badanUsahaPivot->map(fn ($p) => '('.collect($p->cakupan)->implode(', ').')')->implode(''),
                $c->badanUsahaPivot->map(fn ($p) => '('.collect($p->obat_ids)->map(fn ($id) => $c->obatRelasi->firstWhere('id', $id)?->nama)->filter()->implode(', ').')')->implode(''),
                $c->badanUsahaPivot->map(fn ($p) => '('.($p->deskripsi ?? '').')')->implode(''),
                $c->diskon?->first()?->persentase_diskon ?? '',
                $c->penagihanPembayaran?->first()?->jangka_waktu_hari ?? '',
                $c->penagihanPembayaran?->first()?->tipe_hari ?? '',
                $c->tanggal_mulai?->format('Y-m-d') ?? '',
                $c->tanggal_berakhir?->format('Y-m-d') ?? '',
                max(0, $c->days_remaining),
                $c->status,
            ];
        }

        return $data;
    }
}
