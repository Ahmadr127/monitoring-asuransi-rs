<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RoleTableSetting;
use Illuminate\Database\Seeder;

class RoleTableSettingSeeder extends Seeder
{
    public function run()
    {
        // Get all roles
        $roles = Role::all();

        // Default columns for ruang_lingkup table
        $defaultColumns = [
            'nama_asuransi',
            'payor',
            'tpa',
            'badan_usaha',
            'tanggal_mulai',
            'tanggal_berakhir',
            'status',
            'jenis_layanan',
            'benefit',
            'cakupan',
            'diskon',
            'total_tarif',
            'alias_versi_tera',
        ];

        foreach ($roles as $role) {
            RoleTableSetting::updateOrCreate(
                [
                    'role_id' => $role->id,
                    'table_name' => 'ruang_lingkup',
                ],
                [
                    'visible_columns' => $defaultColumns,
                ]
            );
        }
    }
}
