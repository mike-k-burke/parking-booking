<?php

namespace App\Actions\Booking;

use App\Actions\CalendarDay\ShowCalendarDay;
use App\Actions\Customer\SaveCustomer;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StoreBooking
{
    public function __construct(
        private SaveCustomer $saveCustomerAction,
        private ShowCalendarDay $showCalendarDayAction
    ) {}

    public function handle(
        string $registration,
        Carbon $start,
        Carbon $end,
        string $email,
        ?string $mobile = null,
        ?string $password = null
    ): Booking
    {
        if ($start->isAfter($end)) {
            throw new InvalidArgumentException('Start date must not be after end date');
        }

        DB::beginTransaction();

        $customer = $this->saveCustomerAction->handle(compact('email', 'mobile', 'password'));

        $booking = new Booking();
        $booking->fill(['registration' => $registration, 'customer_id' => $customer->id]);
        $booking->save();

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
