<?php

namespace Database\Factories;

use App\Models\Obat;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObatFactory extends Factory
{
    protected $model = Obat::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->word(),
            'kategori' => fake()->randomElement(['Generik', 'Paten', 'Bebas']),
            'satuan' => fake()->randomElement(['Botol', 'Strip', 'Tablet', 'Ampul']),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
