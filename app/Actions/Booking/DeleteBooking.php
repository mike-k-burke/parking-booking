<?php

namespace App\Actions\Booking;

class DeleteBooking
{
    public function __construct(private ShowBooking $showAction) {}

    public function handle(int $id): void
    {
        $booking = $this->showAction->handle($id);
        $booking->delete();
    }
}
