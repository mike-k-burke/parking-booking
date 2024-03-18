<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CalendarDay>
 */
class CalendarDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::createFromTimestamp(fake()->dateTimeBetween('now', '+10 days')->getTimestamp())->startOfDay();

        return [
            'date'              => $date,
            'year'              => (int) $date->format('Y'),
            'month'             => (int) $date->format('n'),
            'day'               => (int) $date->format('j'),
            'day_of_week'       => (int) $date->format('N'),
            'is_weekend'        => $date->isWeekend(),
            'available_spaces'  => fake()->numberBetween(5, 20),
            'price'             => fake()->numberBetween(2000, 10000),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
