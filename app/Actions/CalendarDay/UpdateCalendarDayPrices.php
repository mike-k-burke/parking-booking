<?php

namespace App\Actions\CalendarDay;

use App\Actions\CalendarDay\ShowCalendarDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class UpdateCalendarDayPrices
{
    public function __construct(private ShowCalendarDay $showAction) {}

    public function handle(
        int $price,
        Carbon $start,
        Carbon $end,
        ?array $excludeDays = [],
        ?bool $excludeWeekends = false,
        ?bool $excludeWeekdays = false
    ): bool
    {
        $success = true;

        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            if ($date->isWeekend() && $excludeWeekends) {
                continue;
            }
            if ($date->isWeekday() && $excludeWeekdays) {
                continue;
            }
            if (in_array($date->format('Y-m-d'), $excludeDays)) {
                continue;
            }

            $calendarDay = $this->showAction->handle($date);
            $calendarDay->price = $price;

            if (!$calendarDay->save()) {
                $success = false;
            }
        }

        return $success;
    }
}
