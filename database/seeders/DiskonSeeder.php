<?php

namespace Database\Seeders;

use App\Models\Diskon;
use App\Models\RuangLingkup;
use Illuminate\Database\Seeder;

class DiskonSeeder extends Seeder
{
    public function run(): void
    {
        // Kolom tabel: id, ruang_lingkup_id, persentase_diskon (int), keterangan, timestamps

        $ruangLingkups = RuangLingkup::all();

        if ($ruangLingkups->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada ruang lingkup. Diskon tidak dibuat.');

            return;
        }

        $diskonData = [
            ['persentase' => 15, 'keterangan' => 'Diskon untuk karyawan tetap'],
            ['persentase' => 20, 'keterangan' => 'Diskon untuk keluarga inti'],
            ['persentase' => 25, 'keterangan' => 'Diskon untuk paket tahunan'],
            ['persentase' => 10, 'keterangan' => 'Diskon untuk pembayaran tunai'],
            ['persentase' => 30, 'keterangan' => 'Diskon khusus premium'],
            ['persentase' => 18, 'keterangan' => 'Diskon untuk pembayaran di muka'],
            ['persentase' => 12, 'keterangan' => 'Diskon untuk peserta baru'],
            ['persentase' => 22, 'keterangan' => 'Diskon untuk perpanjangan kontrak'],
            ['persentase' => 8,  'keterangan' => 'Diskon untuk grup korporat'],
            ['persentase' => 5,  'keterangan' => 'Diskon untuk pembayaran reguler'],
        ];

        $totalCreated = 0;
        $highProfileCount = 0;

        foreach ($ruangLingkups as $index => $ruangLingkup) {
            // Tentukan diskon berdasarkan kategori asuransi atau nama
            $nama = strtolower($ruangLingkup->nama_asuransi);
            $isHighProfile = $ruangLingkup->is_high_profile;

            $persentase = 0;
            $keterangan = '';

            if (str_contains($nama, 'bpjs')) {
                // BPJS biasanya tidak ada diskon atau sangat kecil
                $persentase = $index % 2 == 0 ? 0 : 5;
                $keterangan = $persentase > 0 ? 'Diskon Program JKN' : 'Tarif reguler BPJS';
            } elseif ($isHighProfile) {
                // High profile diskon lebih kompetitif
                $options = [
                    ['p' => 25, 'k' => 'Diskon Kerjasama Premium'],
                    ['p' => 30, 'k' => 'Diskon Volume Peserta Besar'],
                    ['p' => 20, 'k' => 'Diskon Loyalitas Klien High Profile'],
                ];
                $opt = $options[$index % count($options)];
                $persentase = $opt['p'];
                $keterangan = $opt['k'];
            } else {
                // Regular diskon
                $options = [
                    ['p' => 10, 'k' => 'Diskon Kerjasama Standar'],
                    ['p' => 15, 'k' => 'Diskon Pembayaran Tepat Waktu'],
                    ['p' => 12, 'k' => 'Diskon Karyawan Korporat'],
                ];
                $opt = $options[$index % count($options)];
                $persentase = $opt['p'];
                $keterangan = $opt['k'];
            }

            if ($persentase > 0) {
                Diskon::firstOrCreate(
                    ['ruang_lingkup_id' => $ruangLingkup->id],
                    [
                        'persentase_diskon' => $persentase,
                        'keterangan' => $keterangan,
                    ],
                );
                $totalCreated++;
                if ($isHighProfile) {
                    $highProfileCount++;
                }
            }
        }

        $this->command->info('✅ Diskon seeded: '.$totalCreated.' records');
        $this->command->info('   - High Profile : '.$highProfileCount);
        $this->command->info('   - Regular      : '.($totalCreated - $highProfileCount));
    }
}
