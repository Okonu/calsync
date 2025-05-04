<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// routes/api.php
Route::middleware(['auth'])->group(function () {
    Route::get('/events', [App\Http\Controllers\CalendarController::class, 'events']);
    Route::get('/calendars', [App\Http\Controllers\CalendarController::class, 'getCalendars']);
    Route::put('/calendars/{calendar}', [App\Http\Controllers\CalendarController::class, 'updateCalendar']);
});
