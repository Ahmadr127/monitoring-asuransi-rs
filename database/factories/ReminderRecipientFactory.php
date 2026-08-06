<?php

namespace Database\Factories;

use App\Models\ReminderRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderRecipientFactory extends Factory
{
    protected $model = ReminderRecipient::class;

    public function definition(): array
    {
        return [
            'email' => fake()->safeEmail(),
            'name' => fake()->name(),
            'category' => fake()->randomElement(['internal', 'manual']),
            'purpose' => fake()->randomElement(['admin', 'legal', 'marketing', 'farmasi', 'kasir', 'pendaftaran']),
            'insurance_name' => null,
            'is_active' => true,
        ];
    }
}
