<?php

namespace Database\Factories;

use App\Models\BadanUsaha;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadanUsahaFactory extends Factory
{
    protected $model = BadanUsaha::class;

    public function definition(): array
    {
        return [
            'nama_bu' => fake()->company(),
        ];
    }
}
