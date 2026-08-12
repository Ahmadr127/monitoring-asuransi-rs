<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['key' => 'admin', 'name' => 'Administrator', 'is_system' => true],
            ['key' => 'inputor', 'name' => 'Penginput Kontrak', 'is_system' => false],
            ['key' => 'legal', 'name' => 'Legal Staff', 'is_system' => false],
            ['key' => 'marketing', 'name' => 'Marketing Staff', 'is_system' => false],
            ['key' => 'farmasi', 'name' => 'Farmasi Staff', 'is_system' => false],
            ['key' => 'kasir', 'name' => 'Kasir Staff', 'is_system' => false],
            ['key' => 'pendaftaran', 'name' => 'Pendaftaran Staff', 'is_system' => false],
        ];

        foreach ($roles as $role) {
            $model = Role::firstOrCreate(['key' => $role['key']], $role);
            $model->update(['name' => $role['name']]);
        }

        $this->command->info('✅ Roles seeded: '.count($roles).' records');
    }
}