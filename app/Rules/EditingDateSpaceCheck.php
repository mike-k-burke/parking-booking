<?php

namespace App\Rules;

use App\Actions\CalendarDay\ShowCalendarDay;
use App\Models\CalendarDay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Closure;

class EditingDateSpaceCheck implements ValidationRule, DataAwareRule
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
    public function validate(string $attribute, mixed $value, Closure $fail, ): void
    {
        if ($value !== null) {
            $availableSpaces = Arr::get($this->data, 'available_spaces');

            /** @var CalendarDay */
            $calendarDay = resolve(ShowCalendarDay::class)->handle(Carbon::createFromFormat('Y-m-d', $value));

            if (!$calendarDay || $calendarDay->booked_spaces > $availableSpaces) {
                $fail('Unable to adjust available spaces for the date ' . $value . ', too many bookings present');
            }
        }
    }
}
