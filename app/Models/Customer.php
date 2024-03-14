<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Customer
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 *
 * @property integer id
 * @property string email
 * @property string mobile
 * @property string password
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property Booking[]|Collection bookings
 */
class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'mobile',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->orderBy('date');
    }
}
