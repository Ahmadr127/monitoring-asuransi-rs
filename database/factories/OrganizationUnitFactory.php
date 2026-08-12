<?php

namespace Database\Factories;

use App\Models\OrganizationType;
use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationUnitFactory extends Factory
{
    protected $model = OrganizationUnit::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'code' => strtoupper(fake()->unique()->lexify('UNI???')),
            'type_id' => OrganizationType::factory(),
            'parent_id' => null,
            'head_id' => null,
            'description' => fake()->sentence(5),
            'is_active' => true,
        ];
    }
}