<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'nip' => fake()->numerify('################'),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => Role::factory(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attrs) => [
            'role_id' => Role::factory()->admin(),
        ]);
    }

    public function nonAdmin(): static
    {
        return $this->state(fn (array $attrs) => [
            'role_id' => Role::factory()->nonAdmin(),
        ]);
    }
}
