<?php

namespace App\Rules;

use App\Actions\Booking\ShowBooking;
use App\Actions\CalendarDay\ShowCalendarDay;
use App\Models\Booking;
use App\Models\CalendarDay;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Closure;

class DateRangeAvailable implements ValidationRule, DataAwareRule
{
    protected $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $editingBooking = Arr::get($this->data, 'booking');
        if ($editingBooking !== null) {
            /** @var Booking */
            $editingBooking = resolve(ShowBooking::class)->handle($editingBooking);
        }
        $start  = Arr::get($this->data, 'start');
        $end    = Arr::get($this->data, 'end');

        if ($start !== $end) {
            /**
             * If a booking is being edited and a new start date or end date is not passed, use the existing date off the booking.
             * Do not validate the start date itself, that will be validated using the DateAvailable rule for the start field.
             */
            $start  = $start === null && $editingBooking !== null ? $editingBooking->start : Carbon::createFromFormat('Y-m-d', $start)->startOfDay()->addDay();
            $end    = $end === null && $editingBooking !== null ? $editingBooking->end : Carbon::createFromFormat('Y-m-d', $end)->startOfDay();
            $period = CarbonPeriod::create($start, $end);

            foreach ($period as $date) {
                /** @var CalendarDay */
                $calendarDay = resolve(ShowCalendarDay::class)->handle(Carbon::createFromFormat('Y-m-d', $date));

                if (!$calendarDay || !$calendarDay->hasFreeSpaces($editingBooking->id)) {
                    $fail('No available spaces for the date ' . $date->format('Y-m-d'));
                }
            }
        }
    }
}
