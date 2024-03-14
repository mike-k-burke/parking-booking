<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customer = Customer::factory()->make();

        return [
            'customer_id'   => Customer::factory(),
            'registration'  => fake()->unique()->regexify('[A-Z]{2}\d{2}[A-Z]{3}'),
        ];
    }
}
