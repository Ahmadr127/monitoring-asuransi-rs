<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard' => 'Dashboard',
            'kerjasama' => 'Kelola Kerjasama',
            'kerjasama_read' => 'Lihat Kerjasama',
            'approval' => 'Persetujuan Kontrak',
            'workflow' => 'Kelola Workflow',
            'departemen' => 'Kelola Departemen',
            'master' => 'Kelola Master Data',
            'reminder' => 'Kelola Reminder',
            'users' => 'Kelola User',
            'roles' => 'Kelola Role & Permission',
            'pengaturan' => 'Pengaturan Kolom',
            'laporan' => 'Laporan & Export',
            'notifikasi' => 'Notifikasi',
        ];

        $permissionModels = [];
        foreach ($permissions as $name => $display) {
            $permissionModels[$name] = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $display, 'description' => 'Akses ke fitur '.$display]
            );
        }

        $assign = [
            'admin' => array_keys($permissions),
            'inputor' => ['dashboard', 'kerjasama', 'kerjasama_read'],
            'legal' => ['dashboard', 'kerjasama_read', 'approval', 'notifikasi', 'laporan'],
            'marketing' => ['dashboard', 'kerjasama_read'],
            'farmasi' => ['dashboard', 'kerjasama_read'],
            'kasir' => ['dashboard', 'kerjasama_read'],
            'pendaftaran' => ['dashboard', 'kerjasama_read'],
        ];

        foreach ($assign as $key => $names) {
            $role = Role::where('key', $key)->first();
            if (! $role) {
                continue;
            }

            $ids = collect($names)->map(fn ($n) => $permissionModels[$n]->id)->all();
            $role->permissions()->sync($ids);
        }

        $this->command->info('✅ Permissions seeded: '.count($permissions).' records + role mapping');
    }
}