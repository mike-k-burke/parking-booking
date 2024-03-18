<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $password = fake()->optional()->password();

        return [
            'email'         => fake()->unique()->safeEmail(),
            'mobile'        => fake()->optional()->e164PhoneNumber(),
            'password'      => $password ? Hash::make($password) : $password,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
