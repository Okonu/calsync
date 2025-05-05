<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\SettingsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => true,
            'canRegister' => true,
        ]);
    });

    Route::get('/login', function () {
        return redirect()->route('google.redirect');
    })->name('login');

    Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/api/events', [CalendarController::class, 'events']);
    Route::get('/api/events/{id}', [CalendarController::class, 'eventDetails']);
    Route::patch('/api/calendars/{calendar}/visibility', [CalendarController::class, 'updateCalendarVisibility']);
    Route::patch('/api/calendars/{calendar}/color', [CalendarController::class, 'updateCalendarColor']);

    Route::get('/api/accounts', [CalendarController::class, 'getAccounts']);
    Route::get('/api/calendars', [CalendarController::class, 'getCalendars']);

    Route::get('connect/google', [GoogleAuthController::class, 'redirectConnect'])->name('google.connect.redirect');
    Route::get('connect/google/callback', [GoogleAuthController::class, 'callbackConnect'])->name('google.connect.callback');

    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');
});

// Settings
Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::patch('/api/accounts/{id}/color', [SettingsController::class, 'updateAccountColor']);
    Route::patch('/api/accounts/{id}/status', [SettingsController::class, 'updateAccountStatus']);
    Route::post('/api/accounts/{id}/sync', [SettingsController::class, 'syncAccount']);
    Route::delete('/api/accounts/{id}', [SettingsController::class, 'deleteAccount']);
});

// Booking
Route::middleware(['auth'])->group(function () {
    Route::get('/booking/settings', [BookingController::class, 'settings'])->name('booking.settings');
    Route::post('/booking/settings', [BookingController::class, 'updateSettings'])->name('booking.update-settings');
    Route::get('/booking/list', [BookingController::class, 'listBookings'])->name('booking.list');
});

Route::get('/book/{slug}', [BookingController::class, 'show'])->name('booking.show');
Route::get('/book/{slug}/slots', [BookingController::class, 'getAvailableSlots'])->name('booking.slots');
Route::post('/book/{slug}', [BookingController::class, 'createBooking'])->name('booking.create');
Route::post('/book/cancel/{uid}', [BookingController::class, 'cancelBooking'])->name('booking.cancel');

Route::get('/book/cancel/{uid}', [BookingController::class, 'cancelBookingPage'])->name('booking.cancel.page');
Route::post('/book/cancel/{uid}', [BookingController::class, 'cancelBooking'])->name('booking.cancel');
