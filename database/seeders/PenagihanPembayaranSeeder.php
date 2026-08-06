<?php

namespace Database\Seeders;

use App\Models\PenagihanPembayaran;
use App\Models\RuangLingkup;
use Illuminate\Database\Seeder;

class PenagihanPembayaranSeeder extends Seeder
{
    public function run(): void
    {
        // Kolom tabel: id, ruang_lingkup_id, jangka_waktu_hari (int), keterangan, timestamps

        $ruangLingkups = RuangLingkup::all();

        if ($ruangLingkups->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada ruang lingkup. Penagihan Pembayaran tidak dibuat.');

            return;
        }

        $options = [
            ['hari' => 7,  'keterangan' => 'Pembayaran tagihan dalam 7 hari kerja sejak invoice diterbitkan'],
            ['hari' => 14,  'keterangan' => 'Pembayaran tagihan dalam 14 hari kerja sejak invoice diterbitkan'],
            ['hari' => 21,  'keterangan' => 'Pembayaran tagihan dalam 21 hari kerja sejak invoice diterbitkan'],
            ['hari' => 30,  'keterangan' => 'Pembayaran tagihan dalam 30 hari kalender sejak invoice diterbitkan'],
            ['hari' => 45,  'keterangan' => 'Pembayaran tagihan dalam 45 hari kalender sejak invoice diterbitkan'],
            ['hari' => 60,  'keterangan' => 'Pembayaran tagihan dalam 60 hari kalender sejak invoice diterbitkan'],
            ['hari' => 90,  'keterangan' => 'Pembayaran tagihan dalam 90 hari kalender sejak invoice diterbitkan'],
        ];

        $totalCreated = 0;
        $highProfileCount = 0;

        foreach ($ruangLingkups as $index => $ruangLingkup) {
            $nama = strtolower($ruangLingkup->nama_asuransi);
            $isHighProfile = $ruangLingkup->is_high_profile;

            // BPJS biasanya 15-30 hari kalender
            if (str_contains($nama, 'bpjs')) {
                $jangkaWaktu = 30;
                $tipeHari = 'Kalender';
                $keterangan = 'Sesuai regulasi BPJS (30 hari)';
            } elseif ($isHighProfile) {
                // High profile biasanya punya term lebih panjang (45-60-90 hari)
                $hpOptions = [
                    ['h' => 45, 't' => 'Kalender', 'k' => 'Term pembayaran 45 hari (High Profile)'],
                    ['h' => 60, 't' => 'Kalender', 'k' => 'Term pembayaran 60 hari (Prioritas)'],
                    ['h' => 30, 't' => 'Hari Kerja', 'k' => '30 hari kerja setelah invoice diterima'],
                ];
                $opt = $hpOptions[$index % count($hpOptions)];
                $jangkaWaktu = $opt['h'];
                $tipeHari = $opt['t'];
                $keterangan = $opt['k'];
            } else {
                // Regular biasanya 14-30 hari
                $regOptions = [
                    ['h' => 14, 't' => 'Hari Kerja', 'k' => '14 hari kerja (Standar)'],
                    ['h' => 30, 't' => 'Kalender', 'k' => '30 hari kalender'],
                    ['h' => 21, 't' => 'Kalender', 'k' => '21 hari kalender'],
                ];
                $opt = $regOptions[$index % count($regOptions)];
                $jangkaWaktu = $opt['h'];
                $tipeHari = $opt['t'];
                $keterangan = $opt['k'];
            }

            PenagihanPembayaran::firstOrCreate(
                ['ruang_lingkup_id' => $ruangLingkup->id],
                [
                    'jangka_waktu_hari' => $jangkaWaktu,
                    'tipe_hari' => $tipeHari,
                    'keterangan' => $keterangan,
                ],
            );

            $totalCreated++;
            if ($isHighProfile) {
                $highProfileCount++;
            }
        }

        $this->command->info('✅ Penagihan Pembayaran seeded: '.$totalCreated.' records');
        $this->command->info('   - High Profile : '.$highProfileCount);
        $this->command->info('   - Regular      : '.($totalCreated - $highProfileCount));
    }
}
