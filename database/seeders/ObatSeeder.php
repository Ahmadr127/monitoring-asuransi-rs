<?php

namespace Database\Seeders;

use App\Models\Obat;
use Illuminate\Database\Seeder;

class ObatSeeder extends Seeder
{
    public function run(): void
    {
        // Kolom tabel: id, nama, kategori, satuan, harga (decimal 15,2), stok (int), deskripsi, timestamps

        $data = [
            // ── Antibiotik ──
            ['nama' => 'Amoxicillin',      'kategori' => 'Antibiotik',      'satuan' => 'Tablet', 'harga' => 5000,    'stok' => 500,  'deskripsi' => 'Antibiotik penisilin untuk infeksi bakteri ringan-sedang'],
            ['nama' => 'Ciprofloxacin',    'kategori' => 'Antibiotik',      'satuan' => 'Tablet', 'harga' => 8000,    'stok' => 300,  'deskripsi' => 'Antibiotik fluorokuinolon spektrum luas'],
            ['nama' => 'Cefadroxil',       'kategori' => 'Antibiotik',      'satuan' => 'Kapsul', 'harga' => 7500,    'stok' => 400,  'deskripsi' => 'Antibiotik sefalosporin generasi pertama'],
            ['nama' => 'Azithromycin',     'kategori' => 'Antibiotik',      'satuan' => 'Tablet', 'harga' => 12000,   'stok' => 250,  'deskripsi' => 'Antibiotik makrolida untuk infeksi saluran napas'],
            ['nama' => 'Metronidazole',    'kategori' => 'Antibiotik',      'satuan' => 'Tablet', 'harga' => 4500,    'stok' => 350,  'deskripsi' => 'Antibiotik untuk infeksi anaerob dan parasit'],
            ['nama' => 'Doxycycline',      'kategori' => 'Antibiotik',      'satuan' => 'Kapsul', 'harga' => 6000,    'stok' => 280,  'deskripsi' => 'Antibiotik tetrasiklin spektrum luas'],

            // ── Obat Jantung & Hipertensi ──
            ['nama' => 'Bisoprolol',       'kategori' => 'Obat Jantung',    'satuan' => 'Tablet', 'harga' => 12000,   'stok' => 250,  'deskripsi' => 'Beta blocker untuk hipertensi dan gagal jantung'],
            ['nama' => 'Captopril',        'kategori' => 'Obat Jantung',    'satuan' => 'Tablet', 'harga' => 3000,    'stok' => 600,  'deskripsi' => 'ACE inhibitor untuk hipertensi dan gagal jantung'],
            ['nama' => 'Simvastatin',      'kategori' => 'Obat Jantung',    'satuan' => 'Tablet', 'harga' => 10000,   'stok' => 350,  'deskripsi' => 'Statin penurun kolesterol LDL'],
            ['nama' => 'Amlodipine',       'kategori' => 'Obat Hipertensi', 'satuan' => 'Tablet', 'harga' => 5000,    'stok' => 700,  'deskripsi' => 'Calcium channel blocker untuk hipertensi'],
            ['nama' => 'Valsartan',        'kategori' => 'Obat Hipertensi', 'satuan' => 'Tablet', 'harga' => 15000,   'stok' => 300,  'deskripsi' => 'ARB untuk hipertensi dan gagal jantung'],
            ['nama' => 'Lisinopril',       'kategori' => 'Obat Hipertensi', 'satuan' => 'Tablet', 'harga' => 9000,    'stok' => 320,  'deskripsi' => 'ACE inhibitor lini pertama untuk hipertensi'],
            ['nama' => 'Atorvastatin',     'kategori' => 'Obat Jantung',    'satuan' => 'Tablet', 'harga' => 18000,   'stok' => 200,  'deskripsi' => 'Statin poten untuk dislipidemia dan pencegahan kardiovaskular'],

            // ── Obat Diabetes ──
            ['nama' => 'Metformin',        'kategori' => 'Obat Diabetes',   'satuan' => 'Tablet', 'harga' => 4000,    'stok' => 800,  'deskripsi' => 'Biguanid lini pertama untuk diabetes tipe 2'],
            ['nama' => 'Glimepiride',      'kategori' => 'Obat Diabetes',   'satuan' => 'Tablet', 'harga' => 6000,    'stok' => 400,  'deskripsi' => 'Sulfonilurea untuk diabetes tipe 2'],
            ['nama' => 'Insulin Glargine', 'kategori' => 'Obat Diabetes',   'satuan' => 'Vial',   'harga' => 250000,  'stok' => 50,   'deskripsi' => 'Insulin long-acting untuk diabetes tipe 1 dan 2'],
            ['nama' => 'Sitagliptin',      'kategori' => 'Obat Diabetes',   'satuan' => 'Tablet', 'harga' => 45000,   'stok' => 150,  'deskripsi' => 'DPP-4 inhibitor untuk diabetes tipe 2'],
            ['nama' => 'Acarbose',         'kategori' => 'Obat Diabetes',   'satuan' => 'Tablet', 'harga' => 8000,    'stok' => 200,  'deskripsi' => 'Penghambat alfa-glukosidase untuk mengontrol gula darah postprandial'],

            // ── Analgesik & Antipiretik ──
            ['nama' => 'Paracetamol',      'kategori' => 'Analgesik',       'satuan' => 'Tablet', 'harga' => 2000,    'stok' => 1000, 'deskripsi' => 'Pereda nyeri dan penurun demam'],
            ['nama' => 'Ibuprofen',        'kategori' => 'Analgesik',       'satuan' => 'Tablet', 'harga' => 3500,    'stok' => 800,  'deskripsi' => 'NSAID pereda nyeri dan anti inflamasi'],
            ['nama' => 'Asam Mefenamat',   'kategori' => 'Analgesik',       'satuan' => 'Tablet', 'harga' => 4000,    'stok' => 600,  'deskripsi' => 'NSAID untuk nyeri sedang, termasuk nyeri haid'],
            ['nama' => 'Tramadol',         'kategori' => 'Analgesik',       'satuan' => 'Kapsul', 'harga' => 12000,   'stok' => 200,  'deskripsi' => 'Opioid lemah untuk nyeri sedang-berat'],
            ['nama' => 'Ketorolac',        'kategori' => 'Analgesik',       'satuan' => 'Tablet', 'harga' => 10000,   'stok' => 180,  'deskripsi' => 'NSAID poten untuk nyeri akut pasca-operasi'],

            // ── Vitamin & Suplemen ──
            ['nama' => 'Vitamin C',        'kategori' => 'Vitamin',         'satuan' => 'Tablet', 'harga' => 5000,    'stok' => 1500, 'deskripsi' => 'Suplemen vitamin C 500 mg untuk imunitas'],
            ['nama' => 'Vitamin B Complex', 'kategori' => 'Vitamin',         'satuan' => 'Tablet', 'harga' => 8000,    'stok' => 1000, 'deskripsi' => 'Suplemen vitamin B1, B6, B12 kompleks untuk saraf'],
            ['nama' => 'Multivitamin',     'kategori' => 'Vitamin',         'satuan' => 'Tablet', 'harga' => 12000,   'stok' => 800,  'deskripsi' => 'Suplemen multivitamin dan mineral lengkap'],
            ['nama' => 'Vitamin D3',       'kategori' => 'Vitamin',         'satuan' => 'Kapsul', 'harga' => 15000,   'stok' => 600,  'deskripsi' => 'Suplemen vitamin D3 1000 IU untuk tulang dan imunitas'],
            ['nama' => 'Asam Folat',       'kategori' => 'Vitamin',         'satuan' => 'Tablet', 'harga' => 3000,    'stok' => 700,  'deskripsi' => 'Suplemen asam folat untuk ibu hamil dan anemia'],

            // ── Obat Saluran Cerna ──
            ['nama' => 'Omeprazole',       'kategori' => 'Obat Maag',       'satuan' => 'Kapsul', 'harga' => 6000,    'stok' => 500,  'deskripsi' => 'Proton pump inhibitor untuk tukak lambung dan GERD'],
            ['nama' => 'Lansoprazole',     'kategori' => 'Obat Maag',       'satuan' => 'Kapsul', 'harga' => 7000,    'stok' => 400,  'deskripsi' => 'Proton pump inhibitor generasi kedua'],
            ['nama' => 'Ranitidine',       'kategori' => 'Obat Maag',       'satuan' => 'Tablet', 'harga' => 4000,    'stok' => 600,  'deskripsi' => 'H2 blocker untuk tukak lambung'],
            ['nama' => 'Antasida',         'kategori' => 'Obat Maag',       'satuan' => 'Tablet', 'harga' => 2500,    'stok' => 800,  'deskripsi' => 'Penetral asam lambung untuk maag ringan'],
            ['nama' => 'Domperidone',      'kategori' => 'Obat Maag',       'satuan' => 'Tablet', 'harga' => 5500,    'stok' => 350,  'deskripsi' => 'Prokinetik untuk mual, muntah, dan kembung'],
            ['nama' => 'Loperamide',       'kategori' => 'Obat Pencernaan', 'satuan' => 'Kapsul', 'harga' => 4500,    'stok' => 300,  'deskripsi' => 'Antidiare untuk diare akut non-spesifik'],

            // ── Obat Batuk, Flu & Alergi ──
            ['nama' => 'OBH Combi',        'kategori' => 'Obat Batuk',      'satuan' => 'Botol',  'harga' => 15000,   'stok' => 200,  'deskripsi' => 'Sirup obat batuk berdahak kombinasi'],
            ['nama' => 'Cetirizine',       'kategori' => 'Antihistamin',    'satuan' => 'Tablet', 'harga' => 3000,    'stok' => 700,  'deskripsi' => 'Antihistamin generasi kedua untuk alergi'],
            ['nama' => 'Pseudoephedrine',  'kategori' => 'Dekongestan',     'satuan' => 'Tablet', 'harga' => 5000,    'stok' => 400,  'deskripsi' => 'Dekongestan untuk hidung tersumbat'],
            ['nama' => 'Loratadine',       'kategori' => 'Antihistamin',    'satuan' => 'Tablet', 'harga' => 4000,    'stok' => 500,  'deskripsi' => 'Antihistamin non-sedatif untuk rhinitis alergi'],
            ['nama' => 'Ambroxol',         'kategori' => 'Obat Batuk',      'satuan' => 'Tablet', 'harga' => 4500,    'stok' => 450,  'deskripsi' => 'Mukolitik pengencer dahak untuk batuk produktif'],

            // ── Obat Kanker & Kemoterapi ──
            ['nama' => 'Tamoxifen',        'kategori' => 'Obat Kanker',     'satuan' => 'Tablet', 'harga' => 50000,   'stok' => 100,  'deskripsi' => 'Antiestrogen untuk kanker payudara hormonal'],
            ['nama' => 'Cisplatin',        'kategori' => 'Obat Kemoterapi', 'satuan' => 'Vial',   'harga' => 500000,  'stok' => 30,   'deskripsi' => 'Agen platinum untuk kemoterapi berbagai kanker'],
            ['nama' => 'Doxorubicin',      'kategori' => 'Obat Kemoterapi', 'satuan' => 'Vial',   'harga' => 750000,  'stok' => 20,   'deskripsi' => 'Antrasiklin untuk kemoterapi kanker payudara dan limfoma'],

            // ── Obat Saraf & Psikiatri ──
            ['nama' => 'Alprazolam',       'kategori' => 'Psikiatri',       'satuan' => 'Tablet', 'harga' => 8000,    'stok' => 150,  'deskripsi' => 'Benzodiazepin untuk ansietas dan gangguan panik'],
            ['nama' => 'Amitriptyline',    'kategori' => 'Psikiatri',       'satuan' => 'Tablet', 'harga' => 5000,    'stok' => 200,  'deskripsi' => 'Antidepresan trisiklik untuk depresi dan nyeri neuropatik'],
            ['nama' => 'Pregabalin',       'kategori' => 'Obat Saraf',      'satuan' => 'Kapsul', 'harga' => 25000,   'stok' => 180,  'deskripsi' => 'Antikonvulsan untuk nyeri neuropatik dan fibromialgia'],

            // ── Cairan & Elektrolit ──
            ['nama' => 'NaCl 0.9%',        'kategori' => 'Cairan Infus',    'satuan' => 'Botol',  'harga' => 20000,   'stok' => 300,  'deskripsi' => 'Cairan infus salin normal untuk rehidrasi'],
            ['nama' => 'Ringer Laktat',    'kategori' => 'Cairan Infus',    'satuan' => 'Botol',  'harga' => 22000,   'stok' => 280,  'deskripsi' => 'Cairan infus isotonik untuk rehidrasi dan elektrolit'],
            ['nama' => 'Dextrose 5%',      'kategori' => 'Cairan Infus',    'satuan' => 'Botol',  'harga' => 18000,   'stok' => 250,  'deskripsi' => 'Cairan infus dekstrosa untuk nutrisi dan hipoglikemia'],
        ];

        foreach ($data as $item) {
            Obat::firstOrCreate(['nama' => $item['nama']], $item);
        }

        $this->command->info('✅ Obat seeded: '.count($data).' records');
    }
}
