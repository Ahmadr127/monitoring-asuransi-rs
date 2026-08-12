<?php

namespace Database\Factories;

use App\Models\OrganizationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationTypeFactory extends Factory
{
    protected $model = OrganizationType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'display_name' => fake()->unique()->words(2, true),
            'level' => 1,
            'description' => fake()->sentence(4),
        ];
    }
}