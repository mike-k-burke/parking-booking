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
 * @property BookingDays[]|Collection booking_days
 * @property Customer customer
 * *
 * @property-read Carbon start
 * @property-read Carbon end
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
        'booking_days'
    ];

    protected $appends = [
        'start',
        'end',
        'price'
    ];

    public function booking_days(): HasMany
    {
        return $this->hasMany(BookingDay::class)->orderBy('date');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getStartAttribute():? Carbon
    {
        if ($this->booking_days->isEmpty()) {
            return null;
        }
        return $this->booking_days->first()->date;
    }

    public function getEndAttribute():? Carbon
    {
        if ($this->booking_days->isEmpty()) {
            return null;
        }
        return $this->booking_days->last()->date;
    }

    public function getPriceAttribute():? int
    {
        if ($this->booking_days->isEmpty()) {
            return null;
        }
        return $this->booking_days->sum('price');
    }
}
