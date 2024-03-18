<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingDay;
use App\Models\CalendarDay;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
        return [
            'customer_id'   => Customer::factory(),
            'registration'  => fake()->unique()->regexify('[A-Z]{2}\d{2}[A-Z]{3}'),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Booking $booking) {
            $dayCount = random_int(1, 4);
            $days = $this->getFreeDays($dayCount);

            BookingDay::factory()
                ->count($dayCount)
                ->sequence(fn ($sequence) => ['date' => $days[$sequence->index]->date, 'price' => $days[$sequence->index]->price, 'booking_id' => $booking->id])
                ->create();
        });
    }

    protected function getFreeDays($dayCount = 1): Collection
    {
        do {
            $startDay = CalendarDay::where('date', '>', now()->startOfDay())->inRandomOrder()->first();
            $days = CalendarDay::where('date', '>=', $startDay->date)->orderBy('date')->take($dayCount)->get();
            $days->filter(fn (CalendarDay $day) => $day->has_free_spaces);
        } while ($days->count() !== $dayCount);

        return $days;
    }
}
