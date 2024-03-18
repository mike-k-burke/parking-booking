<?php

namespace App\Http\Controllers;

use App\Actions\CalendarDay\FetchCalendarDayRange;
use App\Actions\CalendarDay\ShowCalendarDay;
use App\Actions\CalendarDay\UpdateCalendarDayAvailableSpaces;
use App\Actions\CalendarDay\UpdateCalendarDayPrices;
use App\Http\Requests\CalendarDay\UpdateAvailableSpacesRequest;
use App\Http\Requests\CalendarDay\UpdatePriceRequest;
use App\Http\Resources\CalendarDayCollection;
use App\Http\Resources\CalendarDayResource;
use App\Models\CalendarDay;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class CalendarDayController extends Controller
{
    public function show(string $date, ShowCalendarDay $action): JsonResponse
    {
        /**
         * The date must be in the format Y-m-d
         */
        if (date('Y-m-d', strtotime($date)) !== $date) {
            throw (new ModelNotFoundException)->setModel(CalendarDay::class, $date);
        }
        $calendarDay = $action->handle(Carbon::createFromFormat('Y-m-d', $date));
        if ($calendarDay === null) {
            throw (new ModelNotFoundException)->setModel(CalendarDay::class, $date);
        }
        return response()->json(new CalendarDayResource($calendarDay), Response::HTTP_OK);
    }

    public function getRange(string $start, string $end, FetchCalendarDayRange $action)
    {
        /**
         * The dates must be in the format Y-m-d
         */
        if (date('Y-m-d', strtotime($start)) !== $start) {
            throw (new ModelNotFoundException)->setModel(CalendarDay::class, $start);
        }
        if (date('Y-m-d', strtotime($end)) !== $end) {
            throw (new ModelNotFoundException)->setModel(CalendarDay::class, $end);
        }

        $calendarDays = $action->handle(
            Carbon::createFromFormat('Y-m-d', $start)->startOfDay(),
            Carbon::createFromFormat('Y-m-d', $end)->startOfDay()
        );

        if ($calendarDays->count() === 0) {
            throw (new ModelNotFoundException)->setModel(CalendarDay::class);
        }

        return response()->json(new CalendarDayCollection($calendarDays), Response::HTTP_OK);
    }

    public function updateAvailableSpaces(UpdateAvailableSpacesRequest $request, UpdateCalendarDayAvailableSpaces $action): JsonResponse
    {
        $success = $action->handle(
            $request->get('available_spaces'),
            $request->getStart(),
            $request->getEnd(),
            $request->get('exclude_days', []),
            (bool) $request->get('exclude_weekends', false),
            (bool) $request->get('exclude_weekdays', false),
        );
        return response()->json(['success' => $success], Response::HTTP_OK);
    }

    public function updatePrice(UpdatePriceRequest $request, UpdateCalendarDayPrices $action): JsonResponse
    {
        $success = $action->handle(
            $request->get('price'),
            $request->getStart(),
            $request->getEnd(),
            $request->get('exclude_days', []),
            (bool) $request->get('exclude_weekends', false),
            (bool) $request->get('exclude_weekdays', false),
        );
        return response()->json(['success' => $success], Response::HTTP_OK);
    }
}
