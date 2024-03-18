<?php

namespace App\Http\Controllers;

use App\Actions\Booking\DeleteBooking;
use App\Actions\Booking\ShowBooking;
use App\Actions\Booking\StoreBooking;
use App\Actions\Booking\UpdateBooking;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    public function show(string $id, ShowBooking $action): JsonResponse
    {
        $booking = $action->handle($id);
        return response()->json(new BookingResource($booking), Response::HTTP_OK);
    }

    public function store(StoreBookingRequest $request, StoreBooking $action): JsonResponse
    {
        $booking = $action->handle(
            $request->get('registration'),
            $request->getStart(),
            $request->getEnd(),
            $request->get('email'),
            $request->get('mobile'),
            $request->get('password'),
        );

        return response()->json(new BookingResource($booking), Response::HTTP_CREATED);
    }

    public function update(UpdateBookingRequest $request, int $id, UpdateBooking $action): JsonResponse
    {
        $booking = $action->handle(
            $id,
            $request->get('registration'),
            $request->getStart(),
            $request->getEnd(),
        );
        return response()->json(new BookingResource($booking), Response::HTTP_OK);
    }

    public function destroy(int $id, DeleteBooking $action): Response
    {
        $action->handle($id);
        return response()->noContent();
    }
}
