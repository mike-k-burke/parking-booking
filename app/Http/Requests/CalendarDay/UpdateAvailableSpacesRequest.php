<?php

namespace App\Http\Requests\CalendarDay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdateAvailableSpacesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'available_spaces'  => 'required|integer|min:0',
            'start'             => ['required', 'date_format:Y-m-d', 'after:yesterday', 'before_or_equal:end'],
            'end'               => ['required', 'date_format:Y-m-d'],
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
