<?php

namespace App\Actions\CalendarDay;

use App\Models\CalendarDay;
use Carbon\Carbon;

class CheckAvailibility
{
    public function __construct(private FetchCalendarDayRange $fetchRangeAction) {}

    public function handle(Carbon $start, Carbon $end): bool
    {
        $days = $this->fetchRangeAction->handle($start, $end);

        $dayCount = $days->count();

        $freeDays = $days->filter(fn (CalendarDay $day) => $day->has_free_spaces);

        return $dayCount === $freeDays->count();
    }
}
