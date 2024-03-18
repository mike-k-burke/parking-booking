<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\CalendarDay;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingDay>
 */
class BookingDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $day = CalendarDay::where('date', '>', now()->startOfDay())->inRandomOrder()->first();

        return [
            'booking_id'    => Booking::factory(),
            'date'          => $day->date,
            'price'         => $day->price,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
