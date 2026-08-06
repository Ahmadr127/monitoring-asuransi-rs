<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'key' => fake()->unique()->word(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attrs) => [
            'name' => 'Administrator',
            'key' => 'admin',
        ]);
    }

    public function nonAdmin(): static
    {
        return $this->state(fn (array $attrs) => [
            'name' => 'Legal Staff',
            'key' => 'legal',
        ]);
    }
}
