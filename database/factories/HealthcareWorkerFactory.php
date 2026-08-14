<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class HealthcareWorkerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'phone_number' => fake()->unique()->numerify('0812########'),
            'password_hash' => Hash::make('password'),
            'role' => 'bidan',
            'str_number' => fake()->unique()->numerify('STR-####'),
            'status' => 'pending',
            'region_code' => '33.08.05.2009',
            'is_available' => true,
        ];
    }
}
