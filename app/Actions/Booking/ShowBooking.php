<?php

namespace App\Actions\Booking;

use App\Models\Booking;

class ShowBooking
{
    public function handle(int $id): Booking
    {
        return Booking::query()->findOrFail($id);
    }
}
