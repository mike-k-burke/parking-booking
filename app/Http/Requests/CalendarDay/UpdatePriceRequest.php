<?php

namespace App\Http\Requests\CalendarDay;

use App\Rules\DateExists;
use App\Rules\DateRangeExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdatePriceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'price'             => 'required|integer|min:0',
            'start'             => ['bail', 'required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:end', new DateExists],
            'end'               => ['bail', 'required', 'date_format:Y-m-d', new DateRangeExists],
            'exclude_days'      => 'sometimes|array',
            'exclude_days.*'    => 'date_format:Y-m-d',
            'exclude_weekends'  => 'sometimes|boolean',
            'exclude_weekdays'  => 'sometimes|boolean',
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
