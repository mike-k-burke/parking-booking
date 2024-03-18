<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\BookingDay
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 *
 * @property integer id
 * @property integer booking_id
 * @property Carbon date
 * @property integer price
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property Booking booking
 * @property CalendarDay calendar_day
 */
class BookingDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'date',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'price' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function calendar_day(): BelongsTo
    {
        return $this->belongsTo(CalendarDay::class);
    }
}
