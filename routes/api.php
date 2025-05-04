<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth'])->group(function () {
    Route::get('/events', [App\Http\Controllers\CalendarController::class, 'events']);
    Route::get('/calendars', [App\Http\Controllers\CalendarController::class, 'getCalendars']);
    Route::put('/calendars/{calendar}', [App\Http\Controllers\CalendarController::class, 'updateCalendar']);
});

// Booking
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/bookings', function () {
        $user = auth()->user();
        $bookings = $user->bookings()
            ->with('bookingPage', 'calendar')
            ->orderBy('starts_at', 'desc')
            ->get();

        return response()->json($bookings);
    });

    Route::post('/bookings/{id}/cancel', function ($id) {
        $user = auth()->user();
        $booking = Booking::whereHas('bookingPage', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $booking->update(['status' => 'cancelled']);

        if ($booking->google_event_id && $booking->calendar_id) {
            try {
                $calendar = Calendar::findOrFail($booking->calendar_id);
                $googleCalendarService = new GoogleCalendarService($calendar->googleAccount);
                $googleCalendarService->deleteEvent($calendar->google_id, $booking->google_event_id);
            } catch (\Exception $e) {
                \Log::error('Failed to delete calendar event: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'google_event_id' => $booking->google_event_id,
                ]);
            }
        }

        return response()->json(['success' => true]);
    });

});
