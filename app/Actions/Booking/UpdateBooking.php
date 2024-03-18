<?php

namespace App\Actions\Booking;

use App\Actions\CalendarDay\ShowCalendarDay;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateBooking
{
    public function __construct(
        private ShowBooking $showAction,
        private ShowCalendarDay $showCalendarDayAction
    ) {}

    public function handle(
        $id,
        ?string $registration = null,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Booking
    {
        $booking = $this->showAction->handle($id);

        if ($booking->start->isBefore(now()->startOfDay())) {
            throw new InvalidArgumentException('Cannot update active or historical bookings');
        }

        DB::beginTransaction();

        if ($registration !== null) {
            $booking->registration = $registration;
            $booking->save();
        }

        /**
         * Continue if the booking dates have been updated
         */
        if ($start === null && $end === null) {
            DB::commit();
            return $booking;
        }
        if ($start === null) {
            $start = $booking->start;
        }
        if ($end === null) {
            $end = $booking->end;
        }

        if ($start->isBefore(now()->startOfDay())) {
            throw new InvalidArgumentException('Start date must not be in the past');
        }
        if ($start->isAfter($end)) {
            throw new InvalidArgumentException('Start date must not be after end date');
        }

        /**
         * Delete the existing booking day records to create the new booked date range, will assume
         * that a customer editing their booked dates will need to accept current pricing of those dates.
         */
        $booking->booking_days()->delete();

        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $calendarDay = $this->showCalendarDayAction->handle($date);

            if ($calendarDay === null || !$calendarDay->has_free_spaces) {
                DB::rollBack();
                throw new InvalidArgumentException('Invaid date range selected, no available spaces for the date ' . $calendarDay->date->format('Y-m-d'));
            }

            $booking->booking_days()->create([
                'date'  => $calendarDay->date,
                'price' => $calendarDay->price,
            ]);
        }

        DB::commit();
        return $booking->refresh();
    }
}
