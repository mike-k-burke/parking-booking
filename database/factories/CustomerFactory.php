<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'email'         => fake()->unique()->safeEmail(),
            'mobile'        => fake()->optional()->e164PhoneNumber(),
            'password'      => fake()->optional()->password(),
            'created_at'    => Carbon::createFromTimestamp(fake()->dateTimeBetween('-5 days', '-3 days')->getTimestamp()),
            'updated_at'    => Carbon::createFromTimestamp(fake()->dateTimeBetween('-3 days', '-1 days')->getTimestamp()),
        ];
    }
}
