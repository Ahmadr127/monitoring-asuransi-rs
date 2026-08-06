<?php

namespace Database\Seeders;

use App\Models\BadanUsaha;
use Illuminate\Database\Seeder;

class BadanUsahaSeeder extends Seeder
{
    public function run(): void
    {
        // Kolom tabel: id, nama_bu, timestamps
        $data = [
            // ── HIGH PROFILE (sesuai BadanUsaha::getHighProfileList()) ──
            'PT Nayaka Era Husada',
            'PT Mandiri Inhealth',
            'PT Bukit Asam Tbk',
            'PT PLN (Persero)',
            'BPJS Ketenagakerjaan',
            'BPJS Kesehatan',
            'PT Kereta Api Indonesia (Persero)',
            'MEDIKA Yakespen (ANTAM)',
            'PT Telekomunikasi Indonesia (Telkom)',

            // ── BUMN & Pemerintah ──
            'PT Pertamina (Persero)',
            'PT Bank Mandiri (Persero) Tbk',
            'PT Bank Rakyat Indonesia (Persero) Tbk',
            'PT Bank Negara Indonesia (Persero) Tbk',
            'PT Taspen (Persero)',
            'PT Asabri (Persero)',
            'PT Jasa Raharja (Persero)',
            'PT Asuransi Jiwasraya (Persero)',

            // ── Asuransi Swasta ──
            'PT Prudential Life Assurance',
            'PT Allianz Life Indonesia',
            'PT AXA Mandiri Financial Services',
            'PT Asuransi Jiwa Manulife Indonesia',
            'PT Sequis Life',
            'PT Sinarmas MSIG Life',
            'PT Cigna Indonesia',
            'PT FWD Insurance Indonesia',
            'PT Zurich Insurance Indonesia',
            'PT AIA Financial',
            'PT Great Eastern Life Indonesia',
            'PT Asuransi Takaful Keluarga',
            'PT BRI Life',

            // ── TPA / Pengelola Klaim ──
            'PT Asuransi Admedika',
            'PT Asuransi Sinar Mas',

            'PT Asuransi Kesehatan Indonesia',

            // ── Korporat & CV ──
            'CV Medika Sejahtera',
            'CV Karya Medis Utama',
            'CV Mitra Kesehatan',
            'CV Sinar Medika',
            'CV Sehat Bersama',
            'CV Bumi Asih Jaya',
            'CV Medika Nusantara',

            // ── Lain-lain ──
            'Persero',

            // ── Testing / Demo ──
            'Badan Usaha 1',
            'Badan Usaha 2',
            'Badan Usaha 3',
        ];

        foreach ($data as $nama) {
            BadanUsaha::firstOrCreate(['nama_bu' => $nama]);
        }

        $highProfileCount = count(BadanUsaha::getHighProfileList());
        $total = count($data);

        $this->command->info("✅ Badan Usaha seeded: {$total} records");
        $this->command->info("   - High Profile : {$highProfileCount}");
        $this->command->info('   - Regular      : '.($total - $highProfileCount));
    }
}
