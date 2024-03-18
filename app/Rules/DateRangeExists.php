<?php

namespace App\Rules;

use App\Actions\CalendarDay\ShowCalendarDay;
use App\Models\CalendarDay;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Closure;

class DateRangeExists implements ValidationRule, DataAwareRule
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

        if ($start !== null && $end !== null && !$start->isSameDay($end)) {
            /**
             * Do not validate the start date itself, that will be validated using the DateExists rule for the start field.
             */
            $period = CarbonPeriod::create($start->addDay(), $end);

            foreach ($period as $date) {
                /** @var CalendarDay */
                $calendarDay = resolve(ShowCalendarDay::class)->handle($date);

                if (!$calendarDay) {
                    $fail('No calendar found record for date ' . $date->format('Y-m-d'));
                }
            }
        }
    }
}
