<?php

namespace Database\Seeders;

use App\Models\JenisLayanan;
use Illuminate\Database\Seeder;

class JenisLayananSeeder extends Seeder
{
    public function run(): void
    {
        // Kolom tabel: id, nama, kode (unique), deskripsi, timestamps

        $data = [
            ['kode' => 'RI',      'nama' => 'Rawat Inap',        'deskripsi' => 'Layanan perawatan pasien yang memerlukan rawat inap di rumah sakit'],
            ['kode' => 'RJ',      'nama' => 'Rawat Jalan',       'deskripsi' => 'Layanan pemeriksaan dan konsultasi tanpa rawat inap'],
            ['kode' => 'IGD',     'nama' => 'IGD',               'deskripsi' => 'Instalasi Gawat Darurat untuk penanganan kasus emergency'],
            ['kode' => 'ICU',     'nama' => 'ICU',               'deskripsi' => 'Intensive Care Unit – ruang perawatan intensif'],
            ['kode' => 'HCU',     'nama' => 'HCU',               'deskripsi' => 'High Care Unit – ruang perawatan tingkat tinggi'],
            ['kode' => 'NICU',    'nama' => 'NICU',              'deskripsi' => 'Neonatal Intensive Care Unit – perawatan intensif bayi baru lahir'],
            ['kode' => 'ODC',     'nama' => 'One Day Care',      'deskripsi' => 'Layanan perawatan sehari tanpa menginap'],
            ['kode' => 'ODS',     'nama' => 'ODS',               'deskripsi' => 'One Day Surgery – operasi sehari tanpa rawat inap'],
            ['kode' => 'OP',      'nama' => 'Operasi',           'deskripsi' => 'Layanan tindakan operasi dan pembedahan'],
            ['kode' => 'LAB',     'nama' => 'Laboratorium',      'deskripsi' => 'Layanan pemeriksaan laboratorium dan diagnostik'],
            ['kode' => 'RAD',     'nama' => 'Radiologi',         'deskripsi' => 'Layanan pemeriksaan radiologi (Rontgen, CT Scan, MRI, dll)'],
            ['kode' => 'FARM',    'nama' => 'Farmasi',           'deskripsi' => 'Layanan kefarmasian dan penyediaan obat'],
            ['kode' => 'FISIO',   'nama' => 'Fisioterapi',       'deskripsi' => 'Layanan rehabilitasi medik dan fisioterapi'],
            ['kode' => 'MCU',     'nama' => 'Medical Check Up',  'deskripsi' => 'Layanan pemeriksaan kesehatan menyeluruh'],
            ['kode' => 'HD',      'nama' => 'Hemodialisa',       'deskripsi' => 'Layanan cuci darah untuk pasien gagal ginjal'],
            ['kode' => 'ORTOPRO', 'nama' => 'Ortotik-Prostetik', 'deskripsi' => 'Layanan alat bantu gerak dan anggota tubuh buatan'],
            ['kode' => 'EST',     'nama' => 'Estetik',           'deskripsi' => 'Layanan bedah plastik dan kecantikan'],
            ['kode' => 'FERT',    'nama' => 'Fertility',         'deskripsi' => 'Layanan program kehamilan dan fertilitas'],
        ];

        foreach ($data as $item) {
            JenisLayanan::firstOrCreate(['kode' => $item['kode']], $item);
        }

        $this->command->info('✅ Jenis Layanan seeded: '.count($data).' records');
    }
}
