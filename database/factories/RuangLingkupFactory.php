<?php

namespace Database\Factories;

use App\Models\BadanUsaha;
use App\Models\RuangLingkup;
use Illuminate\Database\Eloquent\Factories\Factory;

class RuangLingkupFactory extends Factory
{
    protected $model = RuangLingkup::class;

    public function definition(): array
    {
        return [
            'nama_asuransi' => fake()->company(),
            'penjamin_category' => fake()->randomElement(['TPA', 'PAYOR', 'TPA & PAYOR']),
            'alias_versi_tera' => fake()->optional()->word(),
            'badan_usaha_id' => BadanUsaha::factory(),
            'tanggal_mulai' => fake()->dateTimeBetween('-1 year', '-1 month'),
            'tanggal_berakhir' => fake()->dateTimeBetween('+1 month', '+1 year'),
            'status' => 'aktif',
            'is_high_profile' => false,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attrs) => [
            'tanggal_berakhir' => fake()->dateTimeBetween('-1 year', '-1 day'),
            'status' => 'tidak_aktif',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'tidak_aktif',
        ]);
    }

    public function highProfile(): static
    {
        return $this->state(fn (array $attrs) => [
            'is_high_profile' => true,
        ]);
    }
}
