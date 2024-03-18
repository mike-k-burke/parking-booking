<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarDayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\CalendarDay $this */
        return [
            'date'              => $this->date->format('Y-m-d'),
            'available_spaces'  => $this->available_spaces,
            'booked_spaces'     => $this->booking_days()->count(),
            'has_free_spaces'   => $this->has_free_spaces,
            'price'             => $this->price,
        ];
    }
}
