<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\RoleTableSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleTableSettingFactory extends Factory
{
    protected $model = RoleTableSetting::class;

    public function definition(): array
    {
        return [
            'role_id' => Role::factory(),
            'table_name' => 'ruang_lingkup',
            'visible_columns' => ['nama_asuransi', 'periode', 'status'],
        ];
    }
}
