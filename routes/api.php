<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarDayController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function() {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
});

Route::middleware('auth:sanctum')->group( function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('calendar/{date}', [CalendarDayController::class, 'show'])->name('calendar.show');
    Route::get('calendar/{start}/{end}', [CalendarDayController::class, 'getRange'])->name('calendar.range');
    Route::post('calendar/spaces', [CalendarDayController::class, 'updateAvailableSpaces'])->name('calendar.update.spaces');
    Route::post('calendar/price', [CalendarDayController::class, 'updatePrice'])->name('calendar.update.price');

    Route::resource('bookings', BookingController::class)->only('show', 'store', 'update', 'destroy');
    Route::resource('customers', BookingController::class)->only('show', 'update');
});
