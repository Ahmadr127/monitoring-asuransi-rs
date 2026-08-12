<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'direktorat', 'display_name' => 'Direktorat', 'level' => 1],
            ['name' => 'divisi', 'display_name' => 'Divisi', 'level' => 2],
            ['name' => 'departemen', 'display_name' => 'Departemen', 'level' => 3],
        ];

        foreach ($types as $type) {
            OrganizationType::updateOrCreate(['name' => $type['name']], $type);
        }

        $direktorat = OrganizationType::where('name', 'direktorat')->firstOrFail();
        $departemen = OrganizationType::where('name', 'departemen')->firstOrFail();

        // Head default = user legal
        $legal = User::where('username', 'legal')->first();
        // Anggota default = user penginput
        $penginput = User::where('username', 'penginput')->first();

        $legalDept = OrganizationUnit::updateOrCreate(
            ['code' => 'LEGAL'],
            [
                'name' => 'Legal',
                'type_id' => $departemen->id,
                'parent_id' => null,
                'head_id' => $legal?->id,
                'description' => 'Departemen Legal - review kontrak asuransi.',
                'is_active' => true,
            ]
        );

        OrganizationUnit::updateOrCreate(['code' => 'DIREKSI'], [
            'name' => 'Direksi',
            'type_id' => $direktorat->id,
            'parent_id' => null,
            'head_id' => User::where('username', 'admin')->first()?->id ?? $legal?->id,
            'description' => 'Direksi - persetujuan akhir.',
            'is_active' => true,
        ]);

        if ($penginput) {
            $penginput->update(['organization_unit_id' => $legalDept->id]);
        }

        $this->command->info('✅ Departments seeded: types='.OrganizationType::count().', units='.OrganizationUnit::count());
    }
}