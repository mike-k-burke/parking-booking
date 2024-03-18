<?php

namespace App\Http\Requests\Booking;

use App\Actions\Booking\ShowBooking;
use App\Models\Booking;
use App\Rules\DateAvailable;
use App\Rules\DateRangeAvailable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdateBookingRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        /**
         * Strip any whitespace from the submitted registration and make it all caps
         */
        if ($this->input('registration') !== null) {
            $this->merge(['registration' => strtoupper(preg_replace('/\s/', '', $this->input('registration')))]);
        }
    }

    /**
     * Override the all function to add the booking from the URL to the request, used for date range availibility checking
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function all($keys = null): array
    {
        $request = parent::all($keys);
        $request['booking'] = $this->route('booking');
        return $request;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registration'  => 'sometimes|string|max:15',
            'start'         => ['bail', 'required_with:end', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:end', new DateAvailable],
            'end'           => ['bail', 'required_with:start', 'date_format:Y-m-d', new DateRangeAvailable],
            'booking'       => [
                function ($attribute, $value, $fail) {
                    $booking = resolve(ShowBooking::class)->handle($value);
                    if ($booking->start->isBefore(now()->startOfDay())) {
                        $fail('This booking has already started, you cannot amend an active or historical booking');
                    }
                }
            ],
        ];
    }

    public function getStart():? Carbon
    {
        $start = $this->input('start');
        return $start === null ? $start : Carbon::createFromformat('Y-m-d', $start);
    }

    public function getEnd():? Carbon
    {
        $end = $this->input('end');
        return $end === null ? $end : Carbon::createFromformat('Y-m-d', $end);
    }
}
