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

        if ($start !== $end) {
            /**
             * Do not validate the start date itself, that will be validated using the DateExists rule for the start field.
             */
            $start  = Carbon::createFromFormat('Y-m-d', $start)->startOfDay()->addDay();
            $end    = Carbon::createFromFormat('Y-m-d', $end)->startOfDay();
            $period = CarbonPeriod::create($start, $end);

            foreach ($period as $date) {
                /** @var CalendarDay */
                $calendarDay = resolve(ShowCalendarDay::class)->handle(Carbon::createFromFormat('Y-m-d', $date));

                if (!$calendarDay) {
                    $fail('No calendar found record for date ' . $date->format('Y-m-d'));
                }
            }
        }
    }
}
