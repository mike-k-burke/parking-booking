<?php

namespace App\Actions\CalendarDay;

use App\Models\CalendarDay;
use Carbon\Carbon;

class ShowCalendarDay
{
    public function handle(Carbon $date): CalendarDay
    {
        return CalendarDay::query()->findOrFail($date->startOfDay());
    }
}
