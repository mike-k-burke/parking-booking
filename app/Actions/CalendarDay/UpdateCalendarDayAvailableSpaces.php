<?php

namespace App\Actions\CalendarDay;

use App\Actions\CalendarDays\ShowCalendarDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use InvalidArgumentException;

class UpdateCalendarDayAvailableSpaces
{
    public function __construct(private ShowCalendarDay $showAction) {}

    public function handle(
        int $availableSpaces,
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

            $calendarDay    = $this->showAction->handle($date);
            $bookedDays     = $calendarDay->booking_days()->count();

            if ($bookedDays > $availableSpaces) {
                throw new InvalidArgumentException('Unable to adjust available spaces for ' . $calendarDay->date->format('Y-m-d') . 'to ' . $availableSpaces . ', ' . $bookedDays . ' bookings present.');
            }

            $calendarDay->available_spaces = $availableSpaces;

            if (!$calendarDay->save()) {
                $success = false;
            }
        }

        return $success;
    }
}
