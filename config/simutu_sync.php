<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sinkronisasi User dari Simutu
    |--------------------------------------------------------------------------
    | Simutu adalah satu-satunya sumber data pegawai (master data).
    | Sistem ini MENARIK data dari simutu dan memperbarui tabel users lokal.
    |
    | Prinsip non-destruktif:
    |  - Tidak pernah menghapus user lokal (ada relasi FK seperti user_id).
    |  - Role lokal (role_id) tidak ditimpa buta; hanya diisi saat user BARU.
    |  - Password disalin dari simutu (hash bcrypt kompatibel), satu akun = satu password.
    */

    'enabled' => (bool) env('SIMUTU_SYNC_ENABLED', true),

    'db_connection' => env('SIMUTU_DB_CONNECTION', 'simutu'),

    // Apakah password (hash) dari simutu ikut disalin ke user lokal.
    'sync_password' => true,

    // Status dari simutu yang MEMBOBLOK login di sistem ini.
    // User simutu dengan status selain ini tetap boleh login.
    'block_login_statuses' => ['non-aktif'],

    // Status permintaan atau di luar daftar = boleh login (tidak memblokir).

    // Berapa lama data user dianggap "segar" sebelum lazy-sync saat login (detik).
    'stale_after_seconds' => 3600,

    // Role default untuk user BARU dari simutu yang tidak cocok dengan unit_roles/role_key_map.
    // Diperlukan karena middleware monitoring mengarahkan ulang user tanpa role ke login.
    'default_role' => 'pendaftaran',

    /*
    | Pemetaan unit Simutu -> role lokal (monitoring asuransi).
    | Key dicocokkan dengan str_contains (upper-case).
    | Urutan pertama yang cocok menang.
    */
    'unit_roles' => [
        'LEGAL' => 'legal',
        'MARKETING' => 'marketing',
        'SALES & DIGITAL MARKETING' => 'marketing',
        'FARMASI' => 'farmasi',
        'KASIR' => 'kasir',
        'PENTARIFAN' => 'kasir',
        'AO & CALL CENTER' => 'pendaftaran',
        'ADMISSION' => 'pendaftaran',
        'REKAM MEDIS' => 'pendaftaran',
        'AKUNTANSI' => 'pendaftaran',
        'KEUANGAN' => 'pendaftaran',
    ],

    // Role simutu (tbl_role.nama_role) yang dipetakan langsung ke role lokal.
    // Dicocokkan str_contains (upper-case).
    'role_key_map' => [
        'ADMINISTRATOR' => 'admin',
    ],
];