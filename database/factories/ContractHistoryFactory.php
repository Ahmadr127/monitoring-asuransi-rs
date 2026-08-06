<?php

namespace Database\Factories;

use App\Models\ContractHistory;
use App\Models\RuangLingkup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractHistoryFactory extends Factory
{
    protected $model = ContractHistory::class;

    public function definition(): array
    {
        return [
            'ruang_lingkup_id' => RuangLingkup::factory(),
            'action_type' => fake()->randomElement(['status_change', 'rollover', 'update']),
            'old_end_date' => fake()->date(),
            'new_end_date' => fake()->date(),
            'old_status' => fake()->randomElement(['aktif', 'tidak_aktif']),
            'new_status' => 'aktif',
            'notes' => fake()->sentence(),
        ];
    }
}
