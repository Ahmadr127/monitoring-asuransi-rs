<?php

namespace Database\Factories;

use App\Models\Diskon;
use App\Models\RuangLingkup;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiskonFactory extends Factory
{
    protected $model = Diskon::class;

    public function definition(): array
    {
        return [
            'ruang_lingkup_id' => RuangLingkup::factory(),
            'persentase_diskon' => fake()->numberBetween(1, 50),
            'keterangan' => fake()->sentence(),
        ];
    }
}
