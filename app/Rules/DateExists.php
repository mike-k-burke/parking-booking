<?php

namespace App\Rules;

use App\Actions\CalendarDay\ShowCalendarDay;
use App\Models\CalendarDay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Closure;

class DateExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail, ): void
    {
        if ($value !== null) {
            /** @var CalendarDay */
            $calendarDay = resolve(ShowCalendarDay::class)->handle(Carbon::createFromFormat('Y-m-d', $value));

            if (!$calendarDay) {
                $fail('No calendar found record for date ' . $value);
            }
        }
    }
}
