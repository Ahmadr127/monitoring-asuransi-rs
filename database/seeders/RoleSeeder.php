<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['key' => 'admin', 'name' => 'Administrator'],
            ['key' => 'legal', 'name' => 'Legal Staff'],
            ['key' => 'marketing', 'name' => 'Marketing Staff'],
            ['key' => 'farmasi', 'name' => 'Farmasi Staff'],
            ['key' => 'kasir', 'name' => 'Kasir Staff'],
            ['key' => 'pendaftaran', 'name' => 'Pendaftaran Staff'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['key' => $role['key']], $role);
        }

        $this->command->info('✅ Roles seeded: '.count($roles).' records');
    }
}
