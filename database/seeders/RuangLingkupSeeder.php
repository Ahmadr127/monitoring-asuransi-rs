<?php

namespace Database\Seeders;

use App\Models\BadanUsaha;
use App\Models\JenisLayanan;
use App\Models\Obat;
use App\Models\RuangLingkup;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RuangLingkupSeeder extends Seeder
{
    public function run(): void
    {
        $ruangLingkupData = [
            // ===== MULTI-BU DEMO: 1 Asuransi dengan 3 Badan Usaha berbeda =====
            [
                'nama_asuransi' => 'Asuransi Demo Multi BU',
                'alias_versi_tera' => 'Multi BU v1',
                'penjamin_category' => 'TPA & PAYOR',
                'badan_usaha_groups' => [
                    [
                        'nama_bu' => 'Badan Usaha 1',
                        'jenis_layanan' => ['Rawat Inap', 'Rawat Jalan'],
                        'obat' => ['Paracetamol', 'Amoxicillin'],
                        'benefit' => ['Biaya Kamar', 'Biaya Dokter', 'Biaya Administrasi'],
                        'cakupan' => ['Karyawan Tetap'],
                    ],
                    [
                        'nama_bu' => 'Badan Usaha 2',
                        'jenis_layanan' => ['IGD', 'One Day Care', 'Laboratorium'],
                        'obat' => ['Metformin', 'Amlodipine', 'Vitamin C'],
                        'benefit' => ['Biaya Operasi', 'Biaya ICU', 'Biaya Laboratorium'],
                        'cakupan' => ['Keluarga Karyawan'],
                    ],
                    [
                        'nama_bu' => 'Badan Usaha 3',
                        'jenis_layanan' => ['Medical Check Up', 'Radiologi'],
                        'obat' => ['Vitamin B Complex', 'Multivitamin'],
                        'benefit' => ['Biaya MCU', 'Biaya Rontgen', 'Biaya Konsultasi'],
                        'cakupan' => ['Pensiunan'],
                    ],
                ],
                'deskripsi' => 'Demo: 1 kontrak dengan 3 Badan Usaha, masing-masing punya benefit & cakupan berbeda',
                'tanggal_mulai' => Carbon::now()->subMonths(3),
                'tanggal_berakhir' => Carbon::now()->addMonths(9),
                'status' => 'aktif',
                'is_high_profile' => false,
            ],

            [
                'nama_asuransi' => 'Nayaka',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Nayaka Era Husada',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'One Day Care',
                ],
                'obat' => [
                    'Paracetamol',
                    'Amoxicillin',
                    'Metformin',
                    'Amlodipine',
                ],
                'benefit' => [
                    'Biaya Kamar VIP',
                    'Biaya Dokter Spesialis',
                    'Biaya Operasi',
                    'Biaya Laboratorium',
                ],
                'cakupan' => ['Karyawan Nayaka dan Keluarga'],
                'deskripsi' => 'Kerjasama asuransi kesehatan untuk karyawan Nayaka (High Profile)',
                'tanggal_mulai' => Carbon::now()->subMonths(6),
                'tanggal_berakhir' => Carbon::now()->addMonths(6),
                'status' => 'aktif',
                'is_high_profile' => true,
            ],
            [
                'nama_asuransi' => 'MI (Mandiri Inhealth)',
                'penjamin_category' => 'TPA & PAYOR',
                'badan_usaha' => 'PT Mandiri Inhealth',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'Medical Check Up',
                ],
                'obat' => [
                    'Amlodipine',
                    'Valsartan',
                    'Simvastatin',
                    'Metformin',
                ],
                'benefit' => [
                    'Biaya Kamar',
                    'Biaya Tindakan',
                    'Biaya Laboratorium',
                    'Biaya Obat',
                ],
                'cakupan' => ['Nasabah Mandiri Inhealth Premium'],
                'deskripsi' => 'Asuransi kesehatan Mandiri Inhealth (High Profile)',
                'tanggal_mulai' => Carbon::now()->subMonths(8),
                'tanggal_berakhir' => Carbon::now()->addMonths(4),
                'status' => 'aktif',
                'is_high_profile' => true,
            ],
            [
                'nama_asuransi' => 'Bukit Asam',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Bukit Asam Tbk',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'One Day Care',
                    'Laboratorium',
                ],
                'obat' => [
                    'Paracetamol',
                    'Amoxicillin',
                    'Captopril',
                    'Bisoprolol',
                ],
                'benefit' => [
                    'Biaya Kamar VVIP',
                    'Biaya Dokter',
                    'Biaya Operasi',
                    'Biaya ICU',
                ],
                'cakupan' => ['Karyawan PT Bukit Asam dan Keluarga'],
                'deskripsi' => 'Kerjasama asuransi kesehatan untuk karyawan PT Bukit Asam (High Profile)',
                'tanggal_mulai' => Carbon::now()->subMonths(10),
                'tanggal_berakhir' => Carbon::now()->addMonths(2),
                'status' => 'aktif',
                'is_high_profile' => true,
            ],
            [
                'nama_asuransi' => 'PLN',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT PLN (Persero)',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'Radiologi',
                    'Laboratorium',
                ],
                'obat' => [
                    'Metformin',
                    'Glimepiride',
                    'Amlodipine',
                    'Simvastatin',
                ],
                'benefit' => [
                    'Biaya Kamar VIP',
                    'Biaya Dokter Spesialis',
                    'Biaya Tindakan',
                    'Biaya Ambulans',
                ],
                'cakupan' => ['Karyawan PLN dan Keluarga'],
                'deskripsi' => 'Kerjasama asuransi kesehatan untuk karyawan PLN (High Profile)',
                'tanggal_mulai' => Carbon::now()->subMonths(5),
                'tanggal_berakhir' => Carbon::now()->addMonths(7),
                'status' => 'aktif',
                'is_high_profile' => true,
            ],
            [
                'nama_asuransi' => 'BPJS TK (Ketenagakerjaan)',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'BPJS Ketenagakerjaan',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'One Day Care',
                ],
                'obat' => [
                    'Paracetamol',
                    'Amoxicillin',
                    'Ibuprofen',
                    'Vitamin C',
                ],
                'benefit' => [
                    'Jaminan Kecelakaan Kerja',
                    'Jaminan Kematian',
                    'Jaminan Hari Tua',
                    'Jaminan Pensiun',
                ],
                'cakupan' => ['Seluruh Peserta BPJS Ketenagakerjaan'],
                'deskripsi' => 'Program Jaminan Sosial Ketenagakerjaan (High Profile)',
                'tanggal_mulai' => Carbon::now()->subYear(),
                'tanggal_berakhir' => Carbon::now()->addMonths(6),
                'status' => 'aktif',
                'is_high_profile' => true,
            ],
            [
                'nama_asuransi' => 'BPJS Kesehatan',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'BPJS Kesehatan',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'One Day Care',
                    'Hemodialisa',
                ],
                'obat' => [
                    'Paracetamol',
                    'Amoxicillin',
                    'Metformin',
                    'Amlodipine',
                    'Captopril',
                ],
                'benefit' => [
                    'Pelayanan Kesehatan Tingkat Pertama',
                    'Pelayanan Kesehatan Rujukan Tingkat Lanjutan',
                    'Pelayanan Gawat Darurat',
                ],
                'cakupan' => ['Seluruh Peserta BPJS Kesehatan'],
                'deskripsi' => 'Program Jaminan Kesehatan Nasional (High Profile)',
                'tanggal_mulai' => Carbon::now()->subYear(),
                'tanggal_berakhir' => Carbon::now()->addYear(),
                'status' => 'aktif',
                'is_high_profile' => true,
            ],
            [
                'nama_asuransi' => 'KAI (Kereta Api Indonesia)',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Kereta Api Indonesia (Persero)',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'Medical Check Up',
                ],
                'obat' => [
                    'Bisoprolol',
                    'Captopril',
                    'Simvastatin',
                    'Omeprazole',
                ],
                'benefit' => [
                    'Biaya Kamar VIP',
                    'Biaya Dokter',
                    'Biaya Operasi',
                    'Biaya Pemeriksaan',
                ],
                'cakupan' => ['Karyawan PT KAI dan Keluarga'],
                'deskripsi' => 'Kerjasama asuransi kesehatan untuk karyawan PT KAI (High Profile)',
                'tanggal_mulai' => Carbon::now()->subMonths(7),
                'tanggal_berakhir' => Carbon::now()->addMonths(5),
                'status' => 'aktif',
                'is_high_profile' => true,
            ],
            [
                'nama_asuransi' => 'MEDIKA Yakespen (ANTAM)',
                'penjamin_category' => 'TPA & PAYOR',
                'badan_usaha' => 'MEDIKA Yakespen (ANTAM)',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'One Day Care',
                    'Laboratorium',
                ],
                'obat' => [
                    'Amoxicillin',
                    'Ciprofloxacin',
                    'Paracetamol',
                    'Ibuprofen',
                ],
                'benefit' => [
                    'Biaya Kamar VVIP',
                    'Biaya Dokter Spesialis',
                    'Biaya Operasi',
                    'Biaya Laboratorium',
                ],
                'cakupan' => ['Karyawan ANTAM dan Keluarga'],
                'deskripsi' => 'Kerjasama asuransi kesehatan untuk karyawan ANTAM melalui Yakespen (High Profile)',
                'tanggal_mulai' => Carbon::now()->subMonths(9),
                'tanggal_berakhir' => Carbon::now()->addMonths(3),
                'status' => 'aktif',
                'is_high_profile' => true,
            ],
            [
                'nama_asuransi' => 'STELKOM (Telkom)',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Telekomunikasi Indonesia (Telkom)',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'Radiologi',
                    'Medical Check Up',
                ],
                'obat' => [
                    'Metformin',
                    'Amlodipine',
                    'Simvastatin',
                    'Vitamin B Complex',
                ],
                'benefit' => [
                    'Biaya Kamar VIP',
                    'Biaya Dokter',
                    'Biaya Tindakan',
                    'Biaya Obat',
                ],
                'cakupan' => ['Karyawan Telkom dan Keluarga'],
                'deskripsi' => 'Kerjasama asuransi kesehatan untuk karyawan Telkom (High Profile)',
                'tanggal_mulai' => Carbon::now()->subMonths(4),
                'tanggal_berakhir' => Carbon::now()->addMonths(8),
                'status' => 'aktif',
                'is_high_profile' => true,
            ],

            [
                'nama_asuransi' => 'PT. Asuransi Sinar Mas',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Asuransi Kesehatan Indonesia',
                'jenis_layanan' => ['Rawat Inap', 'Rawat Jalan', 'IGD'],
                'obat' => ['Amoxicillin', 'Paracetamol', 'Vitamin C'],
                'benefit' => [
                    'Biaya Dokter',
                    'Biaya Kamar',
                    'Biaya Obat',
                    'Biaya Laboratorium',
                ],
                'cakupan' => [
                    'Karyawan PT. Sinar Mas',
                    'Keluarga Inti (Istri/Suami dan Anak)',
                ],
                'deskripsi' => 'Kerjasama asuransi kesehatan untuk karyawan PT. Sinar Mas dan keluarga',
                'tanggal_mulai' => Carbon::now()->subYear(),
                'tanggal_berakhir' => Carbon::now()->addDays(5),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. Prudential Life Assurance',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'CV Medika Sejahtera',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'One Day Care',
                    'Laboratorium',
                ],
                'obat' => ['Bisoprolol', 'Captopril', 'Simvastatin'],
                'benefit' => [
                    'Biaya Dokter Spesialis',
                    'Biaya Operasi',
                    'Biaya ICU',
                ],
                'cakupan' => ['Nasabah Prudential Premium'],
                'deskripsi' => 'Asuransi kesehatan premium untuk nasabah Prudential',
                'tanggal_mulai' => Carbon::now()->subMonths(10),
                'tanggal_berakhir' => Carbon::now()->addDays(10),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. Allianz Life Indonesia',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Allianz Life Indonesia',
                'jenis_layanan' => ['Rawat Inap', 'Rawat Jalan', 'Radiologi'],
                'obat' => ['Metformin', 'Glimepiride', 'Amlodipine'],
                'benefit' => [
                    'Biaya Konsultasi',
                    'Biaya Pemeriksaan',
                    'Biaya Tindakan',
                ],
                'cakupan' => ['Pemegang Polis Allianz SmartHealth'],
                'deskripsi' => 'Asuransi kesehatan dengan coverage menyeluruh',
                'tanggal_mulai' => Carbon::now()->subMonths(11),
                'tanggal_berakhir' => Carbon::now()->addDays(15),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. AXA Mandiri Financial Services',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT AXA Mandiri Financial Services',
                'jenis_layanan' => ['Rawat Inap', 'IGD', 'Farmasi'],
                'obat' => ['Ibuprofen', 'Asam Mefenamat', 'Omeprazole'],
                'benefit' => [
                    'Biaya Kamar VIP',
                    'Biaya Dokter',
                    'Biaya Ambulans',
                ],
                'cakupan' => ['Nasabah AXA Mandiri Prioritas'],
                'deskripsi' => 'Layanan kesehatan prioritas untuk nasabah AXA Mandiri',
                'tanggal_mulai' => Carbon::now()->subMonths(8),
                'tanggal_berakhir' => Carbon::now()->addDays(20),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. Manulife Indonesia',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Asuransi Jiwa Manulife Indonesia',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'One Day Care',
                    'Fisioterapi',
                ],
                'obat' => ['Ciprofloxacin', 'Cefadroxil', 'Vitamin B Complex'],
                'benefit' => [
                    'Biaya Rawat Inap',
                    'Biaya Pembedahan',
                    'Biaya Perawatan Intensif',
                ],
                'cakupan' => ['Pemegang Polis Manulife MiUltimate HealthCare'],
                'deskripsi' => 'Proteksi kesehatan menyeluruh dari Manulife',
                'tanggal_mulai' => Carbon::now()->subYear(),
                'tanggal_berakhir' => Carbon::now()->addDays(25),
                'status' => 'aktif',
            ],

            [
                'nama_asuransi' => 'PT. Asuransi Jiwa Sequis Life',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Sequis Life',
                'jenis_layanan' => ['Rawat Jalan', 'IGD', 'Farmasi'],
                'obat' => ['Ranitidine', 'Antasida', 'OBH Combi', 'Cetirizine'],
                'benefit' => [
                    'Biaya Obat-obatan',
                    'Biaya Resep Dokter',
                    'Biaya Vitamin',
                ],
                'cakupan' => ['Nasabah Sequis Q Infinite'],
                'deskripsi' => 'Coverage obat dan farmasi untuk nasabah Sequis',
                'tanggal_mulai' => Carbon::now()->subMonths(14),
                'tanggal_berakhir' => Carbon::now()->addDays(60),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. BRI Life',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT BRI Life',
                'jenis_layanan' => ['Rawat Jalan', 'Farmasi', 'Laboratorium'],
                'obat' => ['Multivitamin', 'Vitamin C', 'Vitamin B Complex'],
                'benefit' => [
                    'Biaya Obat Generik',
                    'Biaya Obat Paten',
                    'Biaya Alat Kesehatan',
                ],
                'cakupan' => ['Nasabah BRI Life Simpel'],
                'deskripsi' => 'Asuransi kesehatan dengan fokus farmasi',
                'tanggal_mulai' => Carbon::now()->subMonths(18),
                'tanggal_berakhir' => Carbon::now()->addDays(70),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. Asuransi Jiwasraya',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Asuransi Jiwasraya (Persero)',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'Medical Check Up',
                ],
                'obat' => ['Valsartan', 'Captopril', 'Bisoprolol'],
                'benefit' => [
                    'Biaya Administrasi',
                    'Biaya Pendaftaran',
                    'Biaya Konsultasi',
                ],
                'cakupan' => ['Peserta Jiwasraya Proteksi Sehat'],
                'deskripsi' => 'Asuransi kesehatan dengan sistem cashless',
                'tanggal_mulai' => Carbon::now()->subMonths(16),
                'tanggal_berakhir' => Carbon::now()->addDays(80),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. Asuransi Takaful Keluarga',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Asuransi Takaful Keluarga',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'One Day Care',
                    'Hemodialisa',
                ],
                'obat' => ['Insulin Glargine', 'Metformin', 'Glimepiride'],
                'benefit' => [
                    'Santunan Harian',
                    'Biaya Perawatan',
                    'Biaya Tindakan Medis',
                ],
                'cakupan' => ['Peserta Takaful Salam'],
                'deskripsi' => 'Asuransi syariah dengan sistem pembayaran mudah',
                'tanggal_mulai' => Carbon::now()->subMonths(13),
                'tanggal_berakhir' => Carbon::now()->addDays(85),
                'status' => 'aktif',
            ],

            // ===== 5 DATA BERAKHIR >90 HARI (masih aktif, jangka panjang) =====
            [
                'nama_asuransi' => 'PT. Great Eastern Life Indonesia',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Great Eastern Life Indonesia',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'Radiologi',
                ],
                'obat' => [
                    'Amoxicillin',
                    'Ciprofloxacin',
                    'Paracetamol',
                    'Ibuprofen',
                ],
                'benefit' => [
                    'Biaya Kamar',
                    'Biaya Dokter',
                    'Biaya Obat',
                    'Biaya Pemeriksaan',
                ],
                'cakupan' => ['Nasabah Great Eastern Premium'],
                'deskripsi' => 'Asuransi kesehatan komprehensif',
                'tanggal_mulai' => Carbon::now()->subMonths(6),
                'tanggal_berakhir' => Carbon::now()->addDays(120),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. Cigna Indonesia',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Cigna Indonesia',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'One Day Care',
                    'Fisioterapi',
                ],
                'obat' => ['Omeprazole', 'Ranitidine', 'Antasida'],
                'benefit' => [
                    'Biaya Perawatan',
                    'Biaya Operasi',
                    'Biaya Rehabilitasi',
                ],
                'cakupan' => ['Pemegang Polis Cigna Proteksi Sehat'],
                'deskripsi' => 'Proteksi kesehatan dengan jaringan luas',
                'tanggal_mulai' => Carbon::now()->subMonths(5),
                'tanggal_berakhir' => Carbon::now()->addDays(150),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. FWD Insurance Indonesia',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT FWD Insurance Indonesia',
                'jenis_layanan' => ['Rawat Jalan', 'IGD', 'Laboratorium'],
                'obat' => ['Tamoxifen', 'Cisplatin', 'Multivitamin'],
                'benefit' => [
                    'Biaya Konsultasi',
                    'Biaya Obat',
                    'Biaya Emergency',
                ],
                'cakupan' => ['Nasabah FWD Cancer Protection'],
                'deskripsi' => 'Asuransi kesehatan dengan fokus pencegahan',
                'tanggal_mulai' => Carbon::now()->subMonths(4),
                'tanggal_berakhir' => Carbon::now()->addDays(180),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. Mandiri Inhealth',
                'penjamin_category' => 'TPA & PAYOR',
                'badan_usaha' => 'PT Mandiri Inhealth',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'Medical Check Up',
                ],
                'obat' => ['Amlodipine', 'Valsartan', 'Simvastatin'],
                'benefit' => [
                    'Biaya Kamar',
                    'Biaya Tindakan',
                    'Biaya Laboratorium',
                ],
                'cakupan' => ['Nasabah Mandiri HealthPlus'],
                'deskripsi' => 'Asuransi kesehatan dengan benefit maksimal',
                'tanggal_mulai' => Carbon::now()->subMonths(3),
                'tanggal_berakhir' => Carbon::now()->addDays(240),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'PT. Admedika',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT Asuransi Admedika',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'IGD',
                    'One Day Care',
                    'Farmasi',
                ],
                'obat' => [
                    'Cefadroxil',
                    'Bisoprolol',
                    'Metformin',
                    'Paracetamol',
                ],
                'benefit' => [
                    'Biaya Perawatan Intensif',
                    'Biaya Ambulans',
                    'Biaya Tindakan Darurat',
                ],
                'cakupan' => ['Nasabah Admedika Elite Care'],
                'deskripsi' => 'Asuransi kesehatan premium dengan layanan 24/7',
                'tanggal_mulai' => Carbon::now()->subMonths(2),
                'tanggal_berakhir' => Carbon::now()->addDays(300),
                'status' => 'aktif',
            ],

            // ===== 5 DATA SUDAH BERAKHIR (expired, tidak aktif) =====
            [
                'nama_asuransi' => 'PT. Taspen (Persero)',
                'penjamin_category' => 'PAYOR',
                'jenis_layanan' => ['Rawat Inap', 'Rawat Jalan', 'Farmasi'],
                'obat' => ['Captopril', 'Amlodipine', 'Metformin', 'Vitamin C'],
                'benefit' => ['Biaya Kamar', 'Biaya Dokter', 'Biaya Obat'],
                'cakupan' => ['Peserta Taspen'],
                'deskripsi' => 'Asuransi kesehatan untuk PNS dan Pensiunan',
                'tanggal_mulai' => Carbon::now()->subYear()->subMonths(2),
                'tanggal_berakhir' => Carbon::now()->subMonths(2),
                'status' => 'tidak_aktif',
            ],
            [
                'nama_asuransi' => 'PT. Asabri (Persero)',
                'penjamin_category' => 'PAYOR',
                'jenis_layanan' => ['Rawat Inap', 'IGD', 'Laboratorium'],
                'obat' => ['Amoxicillin', 'Paracetamol', 'Ibuprofen'],
                'benefit' => [
                    'Biaya Perawatan',
                    'Biaya Emergency',
                    'Biaya Ambulans',
                ],
                'cakupan' => ['Anggota TNI/Polri dan Keluarga'],
                'deskripsi' => 'Proteksi kesehatan untuk TNI/Polri',
                'tanggal_mulai' => Carbon::now()->subMonths(15),
                'tanggal_berakhir' => Carbon::now()->subMonths(1),
                'status' => 'tidak_aktif',
            ],
            [
                'nama_asuransi' => 'PT. Zurich Insurance Indonesia',
                'penjamin_category' => 'PAYOR',
                'jenis_layanan' => ['Rawat Jalan', 'One Day Care', 'Radiologi'],
                'obat' => ['OBH Combi', 'Cetirizine', 'Pseudoephedrine'],
                'benefit' => [
                    'Biaya Konsultasi',
                    'Biaya Obat',
                    'Biaya Pemeriksaan Lab',
                ],
                'cakupan' => ['Nasabah Zurich Care'],
                'deskripsi' => 'Asuransi kesehatan dengan jaringan rumah sakit luas',
                'tanggal_mulai' => Carbon::now()->subMonths(18),
                'tanggal_berakhir' => Carbon::now()->subMonths(3),
                'status' => 'tidak_aktif',
            ],
            [
                'nama_asuransi' => 'PT. AIA Financial',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'PT AIA Financial',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'IGD',
                    'Medical Check Up',
                ],
                'obat' => ['Simvastatin', 'Bisoprolol', 'Omeprazole'],
                'benefit' => [
                    'Biaya Administrasi',
                    'Biaya Tindakan',
                    'Biaya Perawatan',
                ],
                'cakupan' => ['Peserta AIA Prime Health'],
                'deskripsi' => 'Asuransi kesehatan dengan benefit komprehensif',
                'tanggal_mulai' => Carbon::now()->subMonths(16),
                'tanggal_berakhir' => Carbon::now()->subDays(45),
                'status' => 'tidak_aktif',
            ],
            [
                'nama_asuransi' => 'PT. Jasa Raharja (Persero)',
                'penjamin_category' => 'PAYOR',
                'badan_usaha' => 'Persero',
                'jenis_layanan' => ['Rawat Inap', 'One Day Care', 'IGD'],
                'obat' => ['Asam Mefenamat', 'Ibuprofen', 'Vitamin B Complex'],
                'benefit' => [
                    'Santunan Kecelakaan',
                    'Biaya Operasi',
                    'Biaya ICU',
                ],
                'cakupan' => ['Korban Kecelakaan Lalu Lintas'],
                'deskripsi' => 'Asuransi kecelakaan lalu lintas',
                'tanggal_mulai' => Carbon::now()->subMonths(13),
                'tanggal_berakhir' => Carbon::now()->subDays(20),
                'status' => 'tidak_aktif',
            ],

            [
                'nama_asuransi' => 'Kerjasama TPA Admedika',
                'penjamin_category' => 'TPA',
                'badan_usaha' => 'PT Asuransi Kesehatan Indonesia',
                'jenis_layanan' => [
                    'Rawat Inap',
                    'Rawat Jalan',
                    'Laboratorium',
                ],
                'obat' => ['Paracetamol', 'Amoxicillin', 'Cetirizine'],
                'benefit' => [
                    'Biaya Dokter',
                    'Biaya Kamar',
                    'Biaya Pemeriksaan',
                ],
                'cakupan' => ['Peserta Program Admedika'],
                'deskripsi' => 'Kerjasama langsung dengan TPA Admedika',
                'tanggal_mulai' => Carbon::now()->subMonths(6),
                'tanggal_berakhir' => Carbon::now()->addMonths(6),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'Kerjasama TPA Mandiri Inhealth',
                'penjamin_category' => 'TPA & PAYOR',
                'badan_usaha' => 'CV Medika Sejahtera',
                'jenis_layanan' => ['Rawat Inap', 'IGD', 'Radiologi'],
                'obat' => ['Metformin', 'Captopril', 'Simvastatin'],
                'benefit' => [
                    'Biaya Konsultasi',
                    'Biaya Tindakan',
                    'Biaya Obat',
                ],
                'cakupan' => ['Peserta Mandiri Inhealth'],
                'deskripsi' => 'Kerjasama dengan TPA Mandiri Inhealth',
                'tanggal_mulai' => Carbon::now()->subMonths(8),
                'tanggal_berakhir' => Carbon::now()->addMonths(4),
                'status' => 'aktif',
            ],
            [
                'nama_asuransi' => 'Kerjasama TPA Sinar Mas',
                'penjamin_category' => 'TPA',
                'badan_usaha' => 'PT Asuransi Kesehatan Indonesia',
                'jenis_layanan' => ['Rawat Inap', 'One Day Care', 'Farmasi'],
                'obat' => ['Omeprazole', 'Lansoprazole', 'Ranitidine'],
                'benefit' => [
                    'Biaya Kamar',
                    'Biaya Operasi',
                    'Biaya Laboratorium',
                ],
                'cakupan' => ['Peserta Sinar Mas Healthcare'],
                'deskripsi' => 'Kerjasama dengan TPA Sinar Mas',
                'tanggal_mulai' => Carbon::now()->subMonths(10),
                'tanggal_berakhir' => Carbon::now()->addMonths(2),
                'status' => 'aktif',
            ],
        ];

        foreach ($ruangLingkupData as $data) {
            // Build BU groups from data
            if (isset($data['badan_usaha_groups'])) {
                $buGroups = $data['badan_usaha_groups'];
            } else {
                $buNames = isset($data['badan_usaha']) ? [$data['badan_usaha']] : [null];
                $buGroups = [];
                foreach ($buNames as $buName) {
                    $buGroups[] = [
                        'nama_bu' => $buName,
                        'jenis_layanan' => $data['jenis_layanan'] ?? [],
                        'obat' => $data['obat'] ?? [],
                        'benefit' => $data['benefit'] ?? [],
                        'cakupan' => $data['cakupan'] ?? [],
                    ];
                }
            }

            $aliasVersiTera = $data['alias_versi_tera'] ?? $data['nama_asuransi'];

            $ruangLingkup = RuangLingkup::create([
                'nama_asuransi' => $data['nama_asuransi'],
                'penjamin_category' => $data['penjamin_category'] ?? 'PAYOR',
                'alias_versi_tera' => $aliasVersiTera,
                'is_high_profile' => $data['is_high_profile'] ?? false,
                'badan_usaha_id' => null,
                'benefit' => [],
                'cakupan' => [],
                'jenis_layanan' => [],
                'obat' => [],
                'deskripsi' => $data['deskripsi'] ?? null,
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_berakhir' => $data['tanggal_berakhir'],
                'status' => $data['status'] ?? 'aktif',
            ]);

            $allLayananIds = [];
            $allObatIds = [];

            foreach ($buGroups as $group) {
                $buName = $group['nama_bu'];
                $bu = $buName ? BadanUsaha::firstOrCreate(['nama_bu' => $buName]) : null;

                $jenisLayananIds = [];
                foreach ($group['jenis_layanan'] as $nama) {
                    $ly = JenisLayanan::where('nama', $nama)->first();
                    if ($ly) {
                        $jenisLayananIds[] = $ly->id;
                    }
                }

                $obatIds = [];
                foreach ($group['obat'] as $nama) {
                    $ob = Obat::where('nama', $nama)->first();
                    if ($ob) {
                        $obatIds[] = $ob->id;
                    }
                }

                if ($bu) {
                    $ruangLingkup->badanUsahaPivot()->create([
                        'badan_usaha_id' => $bu->id,
                        'benefit' => $group['benefit'],
                        'cakupan' => $group['cakupan'],
                        'jenis_layanan_ids' => $jenisLayananIds,
                        'obat_ids' => $obatIds,
                    ]);
                }

                $allLayananIds = array_merge($allLayananIds, $jenisLayananIds);
                $allObatIds = array_merge($allObatIds, $obatIds);
            }

            if (! empty($allLayananIds)) {
                $ruangLingkup->jenisLayananRelasi()->sync(array_unique($allLayananIds));
            }

            if (! empty($allObatIds)) {
                $ruangLingkup->obatRelasi()->sync(array_unique($allObatIds));
            }
        }

        $this->command->info(
            '✅ Ruang Lingkup seeded successfully! Total: '.
            count($ruangLingkupData).
            ' records',
        );
    }
}
