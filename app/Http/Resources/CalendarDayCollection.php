<?php

namespace App\Http\Resources;

use App\Actions\CalendarDay\CheckAvailibility;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CalendarDayCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $start  = $this->collection->first()->date;
        $end    = $this->collection->last()->date;

        $isAvailable = resolve(CheckAvailibility::class)->handle($start, $end);

        return [
            'data' => $this->collection,
            'meta' => [
                'start'         => $start->format('Y-m-d'),
                'end'           => $end->format('Y-m-d'),
                'is_available'  => $isAvailable,
                'price'         => $this->collection->sum('price'),
            ],
        ];
    }
}
