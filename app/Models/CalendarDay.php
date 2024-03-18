<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\CalendarDay
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 *
 * @property Carbon date
 * @property integer year
 * @property integer month
 * @property integer day
 * @property integer day_of_week
 * @property boolean is_weekend
 * @property integer available_spaces
 * @property integer price
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property BookingDays[]|Collection booking_days
 *
 * @property-read integer booked_spaces
 * @property-read boolean has_free_spaces
 * @method boolean hasFreeSpaces(int $excludeBookingId = null)
 */
class CalendarDay extends Model
{
    use HasFactory;

    protected $primaryKey   = 'date';
    protected $keyType      = 'date';
    public $incrementing    = false;

    protected $fillable = [
        'date',
        'year',
        'month',
        'day',
        'day_of_week',
        'is_weekend',
        'available_spaces',
        'price',
    ];

    protected $appends = [
        'booked_spaces',
        'has_free_spaces',
    ];

    protected function casts(): array
    {
        return [
            'date'              => 'date',
            'year'              => 'integer',
            'month'             => 'integer',
            'day'               => 'integer',
            'day_of_week'       => 'integer',
            'is_weekend'        => 'boolean',
            'available_spaces'  => 'integer',
            'price'             => 'integer',
        ];
    }

    public function booking_days(): HasMany
    {
        return $this->hasMany(BookingDay::class, 'date', 'date')->orderBy('created_at');
    }

    public function getBookedSpacesAttribute(): int
    {
        return $this->booking_days()->count();
    }

    public function getHasFreeSpacesAttribute(): bool
    {
        return $this->hasFreeSpaces();
    }

    public function hasFreeSpaces(int $excludeBookingId = null): bool
    {
        $bookedDays = $this->booking_days();
        if ($excludeBookingId !== null) {
            $bookedDays = $bookedDays->where('booking_id', '!=', $excludeBookingId);
        }

        return $bookedDays->count() < $this->available_spaces;
    }
}
