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
 * @property BookingDays[]|Collection bookingDays
 *
 * @property-read boolean hasFreeSpaces
 */
class CalendarDay extends Model
{
    use HasFactory;

    protected $primaryKey   = 'date';
    protected $keyType      = 'date';
    public $incrementing    = false;

    protected $fillable = [
        'available_spaces',
        'price',
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

    public function bookingDays(): HasMany
    {
        return $this->hasMany(BookingDay::class, 'date', 'date')->orderBy('created_at');
    }

    public function getHasFreeSpacesAttribute(): bool
    {
        return $this->bookingDays->count() < $this->available_spaces;
    }
}
