<?php

namespace Database\Factories;

use App\Models\JenisLayanan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JenisLayananFactory extends Factory
{
    protected $model = JenisLayanan::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'nama' => $name,
            'kode' => Str::slug($name),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
