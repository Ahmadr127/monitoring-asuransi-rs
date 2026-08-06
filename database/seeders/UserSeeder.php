<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'key');

        $users = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@azra.com',
                'password' => Hash::make('admin123'),
                'role_id' => $roles['admin'],
            ],
            [
                'name' => 'Legal Staff',
                'username' => 'legal',
                'email' => 'legal@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['legal'],
            ],
            [
                'name' => 'Marketing Staff',
                'username' => 'marketing',
                'email' => 'marketing@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['marketing'],
            ],
            [
                'name' => 'Farmasi Staff',
                'username' => 'farmasi',
                'email' => 'farmasi@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['farmasi'],
            ],
            [
                'name' => 'Kasir Staff',
                'username' => 'kasir',
                'email' => 'kasir@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['kasir'],
            ],
            [
                'name' => 'Pendaftaran Staff',
                'username' => 'pendaftaran',
                'email' => 'pendaftaran@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['pendaftaran'],
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['username' => $data['username']], $data);
        }

        $this->command->info('✅ Users seeded: '.count($users).' records');
    }
}
