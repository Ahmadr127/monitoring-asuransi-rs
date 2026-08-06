<?php

namespace Database\Factories;

use App\Models\PenagihanPembayaran;
use App\Models\RuangLingkup;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenagihanPembayaranFactory extends Factory
{
    protected $model = PenagihanPembayaran::class;

    public function definition(): array
    {
        return [
            'ruang_lingkup_id' => RuangLingkup::factory(),
            'jangka_waktu_hari' => fake()->numberBetween(7, 90),
            'tipe_hari' => fake()->randomElement(['Hari', 'Kalender']),
            'keterangan' => fake()->sentence(),
        ];
    }
}
