<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['info', 'warning', 'success']),
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'icon' => fake()->randomElement(['bell', 'clock', 'check']),
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attrs) => [
            'read_at' => now(),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn (array $attrs) => [
            'read_at' => null,
        ]);
    }
}
