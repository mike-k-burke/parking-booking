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
        $editBooking = Arr::get($this->data, 'booking');
        if ($editBooking !== null) {
            /** @var Booking */
            $editBooking = resolve(ShowBooking::class)->handle($editBooking);
        }
        $start  = Arr::get($this->data, 'start');
        $end    = Arr::get($this->data, 'end');

        /**
         * Confirm that the data passed in the date fields is of the correct format.
         * Necessary because we are using the start date field while validating the end date and 'bail' doesn't work in this case.
         */
        if ($start !== null && date('Y-m-d', strtotime($start)) === $start) {
            $start = Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
        } else {
            $start = null;
        }
        if ($end !== null && date('Y-m-d', strtotime($end)) === $end) {
            $end = Carbon::createFromFormat('Y-m-d', $end)->startOfDay();
        } else {
            $start = null;
        }

        /**
         * If a booking is being edited and a new start date or end date is not passed, use the existing date off the booking.
         */
        if ($editBooking !== null) {
            $start = $start ?? $editBooking->start;
            $end = $end ?? $editBooking->end;
        }

        if ($start !== null && $end !== null && !$start->isSameDay($end)) {
            /**
             * Do not validate the start date itself, that will be validated using the DateAvailable rule for the start field.
             */
            $period = CarbonPeriod::create($start->addDay(), $end);

            foreach ($period as $date) {
                /** @var CalendarDay */
                $calendarDay = resolve(ShowCalendarDay::class)->handle($date);

                if (!$calendarDay || !$calendarDay->hasFreeSpaces($editBooking ? $editBooking->id : null)) {
                    $fail('No available spaces for the date ' . $date->format('Y-m-d'));
                }
            }
        }
    }
}
