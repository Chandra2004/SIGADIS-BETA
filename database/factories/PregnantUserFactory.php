<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PregnantUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'phone_number' => fake()->unique()->numerify('0813########'),
            'full_name' => fake()->name(),
            'otp_verified_at' => now(),
        ];
    }
}
