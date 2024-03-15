<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Booking
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 *
 * @property integer id
 * @property integer customer_id
 * @property string registration
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property BookingDays[]|Collection bookingDays
 * @property Customer customer
 * *
 * @property-read Carbon startDate
 * @property-read Carbon endDate
 * @property-read int price
 */
class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'registration',
    ];

    protected $with = [
        'bookingDays'
    ];

    public function bookingDays(): HasMany
    {
        return $this->hasMany(BookingDay::class)->orderBy('date');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getStartDateAttribute():? Carbon
    {
        if ($this->bookingDays->isEmpty()) {
            return null;
        }
        return $this->bookingDays->first()->date;
    }

    public function getEndDateAttribute():? Carbon
    {
        if ($this->bookingDays->isEmpty()) {
            return null;
        }
        return $this->bookingDays->last()->date;
    }

    public function getPriceAttribute():? int
    {
        if ($this->bookingDays->isEmpty()) {
            return null;
        }
        return $this->bookingDays->sum('price');
    }
}
