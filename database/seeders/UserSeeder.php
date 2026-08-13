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
                'nip' => '198501012006011001',
                'email' => 'admin@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['admin'],
            ],
            [
                'name' => 'Penginput Kontrak',
                'username' => 'penginput',
                'nip' => '199003152011022002',
                'email' => 'penginput@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['inputor'],
            ],
            [
                'name' => 'Legal Staff',
                'username' => 'legal',
                'nip' => '198702122009031003',
                'email' => 'legal@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['legal'],
            ],
            [
                'name' => 'Marketing Staff',
                'username' => 'marketing',
                'nip' => '199205202012042004',
                'email' => 'marketing@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['marketing'],
            ],
            [
                'name' => 'Farmasi Staff',
                'username' => 'farmasi',
                'nip' => '198811252010051005',
                'email' => 'farmasi@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['farmasi'],
            ],
            [
                'name' => 'Kasir Staff',
                'username' => 'kasir',
                'nip' => '199501072016061006',
                'email' => 'kasir@azra.com',
                'password' => Hash::make('rsazra'),
                'role_id' => $roles['kasir'],
            ],
            [
                'name' => 'Pendaftaran Staff',
                'username' => 'pendaftaran',
                'nip' => '199802182017071007',
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
