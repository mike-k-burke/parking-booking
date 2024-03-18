<?php

namespace App\Http\Requests\Booking;

use App\Rules\DateAvailable;
use App\Rules\DateRangeAvailable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreBookingRequest extends FormRequest
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
        /**
         * Strip any whitespace from the submitted email
         */
        if ($this->input('email') !== null) {
            $this->merge(['email' => preg_replace('/\s/', '', $this->input('email'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registration'  => 'required|string|max:15',
            'start'         => ['bail', 'required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:end', new DateAvailable],
            'end'           => ['bail', 'required', 'date_format:Y-m-d', new DateRangeAvailable],
            'email'         => 'required|string|email:rfc',
            'mobile'        => 'sometimes|string',
            'password'      => 'sometimes|string|min:6|confirmed',
        ];
    }

    public function getStart(): Carbon
    {
        return Carbon::createFromformat('Y-m-d', $this->input('start'));
    }

    public function getEnd(): Carbon
    {
        return Carbon::createFromformat('Y-m-d', $this->input('end'));
    }
}
