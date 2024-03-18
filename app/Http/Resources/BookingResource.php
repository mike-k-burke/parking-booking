<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Booking $this */
        return [
            'id'            => $this->id,
            'registration'  => $this->registration,
            'customer'      => new CustomerResource($this->customer),
            'start'         => $this->start->format('Y-m-d'),
            'end'           => $this->end->format('Y-m-d'),
            'price'         => $this->price,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'booking_days'  => BookingDayResource::collection($this->booking_days)
        ];
    }
}
