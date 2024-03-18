<?php

namespace App\Actions\CalendarDay;

use App\Models\CalendarDay;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class FetchCalendarDayRange
{
    public function handle(Carbon $start, Carbon $end): Collection
    {
        $query = CalendarDay::query();

        $query->where('date', '>=', $start);
        $query->where('date', '<=', $end);

        return $query->get();
    }
}
