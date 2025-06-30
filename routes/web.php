<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CallForSpeakersController;
use App\Http\Controllers\CfsApplicationController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityEventController;
use App\Http\Controllers\EventSessionController;
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

Route::get('/help', function () {
    return Inertia::render('HelpCenter');
})->name('help');

Route::get('/privacy-policy', function () {
    return view('pages.privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('pages.terms-of-service');
})->name('terms-of-service');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $userCommunities = $user->activeCommunities()->limit(3)->get();

        return Inertia::render('Dashboard', [
            'communities' => $userCommunities->map(function ($community) {
                return [
                    'id' => $community->id,
                    'name' => $community->name,
                    'slug' => $community->slug,
                    'logo_url' => $community->logo_url,
                    'events_count' => $community->events()->count(),
                    'dashboard_url' => route('communities.dashboard', $community->slug),
                ];
            }),
        ]);
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

// Public community routes (these use slugs)
Route::get('/community/{slug}', [CommunityController::class, 'show'])->name('community.show');
Route::get('/community/{slug}/events', [CommunityEventController::class, 'publicList'])->name('community.events.public');
Route::get('/community/{slug}/events/{event}', [CommunityEventController::class, 'publicShow'])->name('community.events.show.public');
Route::get('/community/{slug}/cfs', [CallForSpeakersController::class, 'publicList'])->name('community.cfs.public');
Route::get('/community/{slug}/cfs/{cfs}', [CallForSpeakersController::class, 'publicShow'])->name('community.cfs.show.public');

// CFS Applications (public)
Route::post('/community/{community}/cfs/{cfs}/apply', [CfsApplicationController::class, 'apply'])->name('cfs.apply');
Route::get('/application/{uid}/track', [CfsApplicationController::class, 'track'])->name('cfs.application.track');
Route::post('/application/{uid}/withdraw', [CfsApplicationController::class, 'withdraw'])->name('cfs.application.withdraw');

// Authenticated community management routes - USING SLUGS WITH MODEL BINDING
Route::middleware(['auth'])->group(function () {

    // Community management
    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
    Route::get('/communities/create', [CommunityController::class, 'create'])->name('communities.create');
    Route::post('/communities', [CommunityController::class, 'store'])->name('communities.store');

    // Main community routes - using slugs with model binding
    Route::get('/communities/{community}', [CommunityController::class, 'dashboard'])->name('communities.dashboard');
    Route::get('/communities/{community}/settings', [CommunityController::class, 'settings'])->name('communities.settings');
    Route::patch('/communities/{community}', [CommunityController::class, 'update'])->name('communities.update');
    Route::delete('/communities/{community}', [CommunityController::class, 'destroy'])->name('communities.destroy');

    // Community Events - using slugs with model binding
    Route::get('/communities/{community}/events', [CommunityEventController::class, 'index'])->name('communities.events.index');
    Route::get('/communities/{community}/events/create', [CommunityEventController::class, 'create'])->name('communities.events.create');
    Route::post('/communities/{community}/events', [CommunityEventController::class, 'store'])->name('communities.events.store');
    Route::get('/communities/{community}/events/{event}', [CommunityEventController::class, 'show'])->name('communities.events.show');
    Route::get('/communities/{community}/events/{event}/edit', [CommunityEventController::class, 'edit'])->name('communities.events.edit');
    Route::patch('/communities/{community}/events/{event}', [CommunityEventController::class, 'update'])->name('communities.events.update');
    Route::delete('/communities/{community}/events/{event}', [CommunityEventController::class, 'destroy'])->name('communities.events.destroy');

    // Event Sessions
    Route::get('/communities/{community}/events/{event}/sessions', [EventSessionController::class, 'index'])->name('communities.events.sessions.index');
    Route::post('/communities/{community}/events/{event}/sessions', [EventSessionController::class, 'store'])->name('communities.events.sessions.store');
    Route::get('/communities/{community}/events/{event}/sessions/{session}', [EventSessionController::class, 'show'])->name('communities.events.sessions.show');
    Route::patch('/communities/{community}/events/{event}/sessions/{session}', [EventSessionController::class, 'update'])->name('communities.events.sessions.update');
    Route::delete('/communities/{community}/events/{event}/sessions/{session}', [EventSessionController::class, 'destroy'])->name('communities.events.sessions.destroy');

    // Call for Speakers - using slugs with model binding
    Route::get('/communities/{community}/cfs', [CallForSpeakersController::class, 'index'])->name('communities.cfs.index');
    Route::get('/communities/{community}/cfs/create', [CallForSpeakersController::class, 'create'])->name('communities.cfs.create');
    Route::post('/communities/{community}/cfs', [CallForSpeakersController::class, 'store'])->name('communities.cfs.store');
    Route::get('/communities/{community}/cfs/{cfs}', [CallForSpeakersController::class, 'show'])->name('communities.cfs.show');
    Route::get('/communities/{community}/cfs/{cfs}/edit', [CallForSpeakersController::class, 'edit'])->name('communities.cfs.edit');
    Route::patch('/communities/{community}/cfs/{cfs}', [CallForSpeakersController::class, 'update'])->name('communities.cfs.update');
    Route::delete('/communities/{community}/cfs/{cfs}', [CallForSpeakersController::class, 'destroy'])->name('communities.cfs.destroy');

    // CFS Status management
//        Route::patch('/communities/{community}/cfs/{cfs}/status', [CallForSpeakersController::class, 'updateStatus']);
    Route::patch('/communities/{community}/cfs/{cfs}/status', [CallForSpeakersController::class, 'updateStatus'])
        ->name('communities.cfs.update-status');

    // AJAX/API endpoints for community management
    Route::prefix('api')->group(function () {

        // Community calendar
        Route::get('/communities/{community}/calendar', [CommunityController::class, 'getCalendar']);

        // Event management
        Route::post('/communities/{community}/events/{event}/speakers', [CommunityEventController::class, 'addSpeaker']);

        // Session management
        Route::post('/communities/{community}/events/{event}/sessions/{session}/speakers', [EventSessionController::class, 'addSpeaker']);
        Route::patch('/communities/{community}/events/{event}/sessions/{session}/speakers/{speaker}', [EventSessionController::class, 'updateSpeaker']);
        Route::delete('/communities/{community}/events/{event}/sessions/{session}/speakers/{speaker}', [EventSessionController::class, 'removeSpeaker']);

        // CFS management
        Route::post('/communities/{community}/cfs/{cfs}/link-event', [CallForSpeakersController::class, 'linkEvent']);
        Route::delete('/communities/{community}/cfs/{cfs}/unlink-event', [CallForSpeakersController::class, 'unlinkEvent']);



        // Application management
        Route::get('/cfs/{cfs}/applications/{application}', [CfsApplicationController::class, 'show']);
        Route::patch('/cfs/{cfs}/applications/{application}/approve', [CfsApplicationController::class, 'approve']);
        Route::patch('/cfs/{cfs}/applications/{application}/reject', [CfsApplicationController::class, 'reject']);
        Route::patch('/cfs/{cfs}/applications/{application}/notes', [CfsApplicationController::class, 'updateNotes']);
        Route::patch('/cfs/{cfs}/applications/bulk-approve', [CfsApplicationController::class, 'bulkApprove']);
        Route::patch('/cfs/{cfs}/applications/bulk-reject', [CfsApplicationController::class, 'bulkReject']);

        // File downloads
        Route::get('/applications/{application}/attachments/{index}', [CfsApplicationController::class, 'downloadAttachment']);
    });
});

Route::get('/debug-communities', function() {
    $user = auth()->user();
    if (!$user) {
        return 'Not logged in';
    }

    $communities = \App\Models\Community::where('user_id', $user->id)->get();

    return [
        'user_id' => $user->id,
        'user_email' => $user->email,
        'communities' => $communities->toArray(),
        'all_communities' => \App\Models\Community::all()->toArray()
    ];
})->middleware('auth');
