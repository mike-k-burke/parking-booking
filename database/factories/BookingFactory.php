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
            'created_at'    => Carbon::createFromTimestamp(fake()->dateTimeBetween('-5 days', '-3 days')->getTimestamp()),
            'updated_at'    => Carbon::createFromTimestamp(fake()->dateTimeBetween('-3 days', '-1 days')->getTimestamp()),
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

    /**
     * Undocumented function
     *
     * @param integer $dayCount
     * @return Collection
     */
    protected function getFreeDays($dayCount = 1): Collection
    {
        do {
            $startDay = CalendarDay::where('date', '>', Carbon::now()->startOfDay())->inRandomOrder()->first();
            $days = CalendarDay::where('date', '>=', $startDay->date)->orderBy('date')->take($dayCount)->get();
            $days->filter(fn (CalendarDay $day) => $day->hasFreeSpaces);
        } while ($days->count() !== $dayCount);

        return $days;
    }
}
