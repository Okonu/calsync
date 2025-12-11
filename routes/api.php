<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;
use App\Models\Booking;
use App\Models\Calendar;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// Public routes
Route::prefix('public')->group(function () {
    Route::get('/communities', [CommunityController::class, 'publicIndex']);
    Route::get('/communities/{slug}', [CommunityController::class, 'publicShow']);
    Route::get('/communities/{slug}/events', [EventController::class, 'publicEventsList']);
    Route::get('/communities/{slug}/events/{eventSlug}', [EventController::class, 'publicEventShow']);
    Route::get('/communities/{slug}/cfs', [CommunityController::class, 'publicCfsList']);
    Route::get('/communities/{slug}/cfs/{cfsSlug}', [CommunityController::class, 'publicCfsShow']);

    // Booking pages
    Route::get('/booking/{slug}', [BookingController::class, 'publicShow']);
    Route::get('/booking/{slug}/slots', [BookingController::class, 'getAvailableSlots']);
    Route::post('/booking/{slug}', [BookingController::class, 'createBooking']);
});

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // User profile
    Route::get('/user', [UserController::class, 'profile']);
    Route::put('/user', [UserController::class, 'updateProfile']);
    Route::get('/user/stats', [UserController::class, 'getStats']);

    // Calendar & Events
    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index']);
        Route::get('/events', [CalendarController::class, 'events']);
        Route::get('/events/{id}', [CalendarController::class, 'eventDetails']);
        Route::get('/calendars', [CalendarController::class, 'getCalendars']);
        Route::put('/calendars/{calendar}', [CalendarController::class, 'updateCalendar']);
        Route::patch('/calendars/{calendar}/visibility', [CalendarController::class, 'updateCalendarVisibility']);
        Route::patch('/calendars/{calendar}/color', [CalendarController::class, 'updateCalendarColor']);
        Route::get('/accounts', [CalendarController::class, 'getAccounts']);
    });

    // Bookings
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::post('/{id}/cancel', [BookingController::class, 'cancel']);
        Route::get('/settings', [BookingController::class, 'getSettings']);
        Route::put('/settings', [BookingController::class, 'updateSettings']);
    });

    // Communities
    Route::prefix('communities')->group(function () {
        Route::get('/', [CommunityController::class, 'index']);
        Route::post('/', [CommunityController::class, 'store']);
        Route::get('/{community}', [CommunityController::class, 'show']);
        Route::put('/{community}', [CommunityController::class, 'update']);
        Route::delete('/{community}', [CommunityController::class, 'destroy']);
        Route::get('/{community}/stats', [CommunityController::class, 'getStats']);
        Route::get('/{community}/calendar', [CommunityController::class, 'getCalendar']);

        // Community Events
        Route::prefix('/{community}/events')->group(function () {
            Route::get('/', [EventController::class, 'index']);
            Route::post('/', [EventController::class, 'store']);
            Route::get('/{event}', [EventController::class, 'show']);
            Route::put('/{event}', [EventController::class, 'update']);
            Route::delete('/{event}', [EventController::class, 'destroy']);
            Route::post('/{event}/speakers', [EventController::class, 'addSpeaker']);
            Route::put('/{event}/speakers/{speaker}', [EventController::class, 'updateSpeaker']);
            Route::delete('/{event}/speakers/{speaker}', [EventController::class, 'removeSpeaker']);

            // Event Sessions
            Route::prefix('/{event}/sessions')->group(function () {
                Route::get('/', [EventController::class, 'getSessions']);
                Route::post('/', [EventController::class, 'createSession']);
                Route::put('/{session}', [EventController::class, 'updateSession']);
                Route::delete('/{session}', [EventController::class, 'deleteSession']);
                Route::post('/{session}/speakers', [EventController::class, 'addSessionSpeaker']);
            });
        });

        // Call for Speakers
        Route::prefix('/{community}/cfs')->group(function () {
            Route::get('/', [CommunityController::class, 'getCfsList']);
            Route::post('/', [CommunityController::class, 'createCfs']);
            Route::get('/{cfs}', [CommunityController::class, 'getCfs']);
            Route::put('/{cfs}', [CommunityController::class, 'updateCfs']);
            Route::delete('/{cfs}', [CommunityController::class, 'deleteCfs']);
            Route::patch('/{cfs}/status', [CommunityController::class, 'updateCfsStatus']);
            Route::get('/{cfs}/applications', [CommunityController::class, 'getCfsApplications']);
            Route::post('/{cfs}/applications/{application}/approve', [CommunityController::class, 'approveCfsApplication']);
            Route::post('/{cfs}/applications/{application}/reject', [CommunityController::class, 'rejectCfsApplication']);
        });
    });

    // Google Account Integration
    Route::prefix('google')->group(function () {
        Route::post('/accounts/{id}/sync', [CalendarController::class, 'syncAccount']);
        Route::patch('/accounts/{id}/color', [CalendarController::class, 'updateAccountColor']);
        Route::patch('/accounts/{id}/status', [CalendarController::class, 'updateAccountStatus']);
        Route::delete('/accounts/{id}', [CalendarController::class, 'deleteAccount']);
    });
});

// Legacy routes for backward compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth'])->group(function () {
    Route::get('/events', [App\Http\Controllers\CalendarController::class, 'events']);
    Route::get('/calendars', [App\Http\Controllers\CalendarController::class, 'getCalendars']);
    Route::put('/calendars/{calendar}', [App\Http\Controllers\CalendarController::class, 'updateCalendar']);
});

// Legacy Booking routes
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
