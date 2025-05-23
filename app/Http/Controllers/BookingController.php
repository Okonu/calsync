<?php

namespace App\Http\Controllers;

use App\Mail\BookingCancellation;
use App\Mail\BookingConfirmation;
use App\Models\User;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\GoogleAccount;
use App\Models\Booking;
use App\Models\BookingPage;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function settings()
    {
        $user = auth()->user();
        $bookingPage = $user->bookingPage ?? new BookingPage();

        $accounts = GoogleAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'email', 'color', 'is_primary']);

        $calendars = Calendar::whereIn('google_account_id', $accounts->pluck('id'))
            ->with('googleAccount:id,name,email')
            ->get();

        return Inertia::render('Booking/Settings', [
            'bookingPage' => $bookingPage->exists ? [
                'id' => $bookingPage->id,
                'title' => $bookingPage->title,
                'description' => $bookingPage->description,
                'slug' => $bookingPage->slug,
                'duration' => $bookingPage->duration,
                'destination_calendar_id' => $bookingPage->destination_calendar_id,
                'available_days' => $bookingPage->available_days,
                'start_time' => $bookingPage->start_time,
                'end_time' => $bookingPage->end_time,
                'buffer_before' => $bookingPage->buffer_before,
                'buffer_after' => $bookingPage->buffer_after,
                'include_meet' => $bookingPage->include_meet,
                'selected_calendars' => $bookingPage->selected_calendars,
            ] : null,
            'accounts' => $accounts,
            'calendars' => $calendars,
            'bookingUrl' => $bookingPage->slug ? url('/book/' . $bookingPage->slug) : null,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'slug' => 'required|string|max:50|alpha_dash|unique:booking_pages,slug,' . ($user->bookingPage->id ?? 0),
            'duration' => 'required|integer|min:5|max:120',
            'destination_calendar_id' => 'nullable|exists:calendars,id',
            'available_days' => 'required|array',
            'available_days.*' => 'integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'buffer_before' => 'nullable|integer|min:0|max:60',
            'buffer_after' => 'nullable|integer|min:0|max:60',
            'include_meet' => 'boolean',
            'selected_calendars' => 'required|array',
            'selected_calendars.*' => 'exists:calendars,id',
        ]);

        $bookingPage = $user->bookingPage ?? new BookingPage();
        $bookingPage->user_id = $user->id;
        $bookingPage->title = $request->title;
        $bookingPage->description = $request->description;
        $bookingPage->slug = $request->slug;
        $bookingPage->duration = $request->duration;
        $bookingPage->destination_calendar_id = $request->destination_calendar_id;
        $bookingPage->available_days = $request->available_days;
        $bookingPage->start_time = $request->start_time;
        $bookingPage->end_time = $request->end_time;
        $bookingPage->buffer_before = $request->buffer_before;
        $bookingPage->buffer_after = $request->buffer_after;
        $bookingPage->include_meet = $request->include_meet;
        $bookingPage->selected_calendars = $request->selected_calendars;
        $bookingPage->save();

        return redirect()->back()->with('success', 'Booking page settings updated successfully!');
    }

    public function show($slug)
    {
        $bookingPage = BookingPage::where('slug', $slug)->firstOrFail();
        $user = $bookingPage->user;

        return Inertia::render('Booking/PublicPage', [
            'bookingPage' => [
                'title' => $bookingPage->title,
                'description' => $bookingPage->description,
                'duration' => $bookingPage->duration,
                'slug' => $bookingPage->slug,
                'user' => [
                    'name' => $user->name,
                ],
            ],
        ]);
    }

    public function getAvailableSlots(Request $request, $slug)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $bookingPage = BookingPage::where('slug', $slug)->firstOrFail();

        // Convert the date to UTC for consistent calculations
        $date = Carbon::parse($request->date)->setTimezone('UTC');

        $dayOfWeek = $date->dayOfWeek;
        if (!in_array($dayOfWeek, $bookingPage->available_days)) {
            return response()->json([
                'slots' => []
            ]);
        }

        $accounts = GoogleAccount::where('user_id', $bookingPage->user_id)
            ->where('is_active', true)
            ->pluck('id');

        $allCalendars = Calendar::whereIn('google_account_id', $accounts)
            ->pluck('id')
            ->toArray();

        $selectedCalendars = !empty($bookingPage->selected_calendars)
            ? $bookingPage->selected_calendars
            : $allCalendars;

        // Convert booking page time settings to UTC for the given date
        $startOfDay = Carbon::parse($date->format('Y-m-d') . ' ' . $bookingPage->start_time, 'UTC');
        $endOfDay = Carbon::parse($date->format('Y-m-d') . ' ' . $bookingPage->end_time, 'UTC');

        // Get events that overlap with this date in UTC
        $events = Event::whereIn('calendar_id', $selectedCalendars)
            ->where(function($query) use ($date) {
                $query->whereDate('starts_at', $date)
                    ->orWhereDate('ends_at', $date);
            })
            ->get();

        Log::debug('Events retrieved', [
            'date' => $date->format('Y-m-d'),
            'event_count' => $events->count(),
            'calendars_checked' => $selectedCalendars
        ]);

        $slots = [];
        $slotDuration = $bookingPage->duration;
        $bufferBefore = $bookingPage->buffer_before ?? 0;
        $bufferAfter = $bookingPage->buffer_after ?? 0;

        $current = $startOfDay->copy();

        while ($current->copy()->addMinutes($slotDuration)->lte($endOfDay)) {
            $slotStart = $current->copy();
            $slotEnd = $slotStart->copy()->addMinutes($slotDuration);

            $slotStartWithBuffer = $slotStart->copy()->subMinutes($bufferBefore);
            $slotEndWithBuffer = $slotEnd->copy()->addMinutes($bufferAfter);

            $isAvailable = true;

            foreach ($events as $event) {
                // Ensure event times are in UTC for comparison
                $eventStart = Carbon::parse($event->starts_at)->setTimezone('UTC');
                $eventEnd = Carbon::parse($event->ends_at)->setTimezone('UTC');

                // Check for overlap
                if (max($slotStartWithBuffer, $eventStart) < min($slotEndWithBuffer, $eventEnd)) {
                    $isAvailable = false;

                    Log::debug('Slot unavailable due to event', [
                        'slot' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                        'event' => $event->title,
                        'event_time' => $eventStart->format('H:i') . ' - ' . $eventEnd->format('H:i')
                    ]);

                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                ];
            }

            $current = $current->copy()->addMinutes($slotDuration);
        }

        return response()->json([
            'slots' => $slots,
            'timezone' => 'UTC',
        ]);
    }

    public function createBooking(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $bookingPage = BookingPage::where('slug', $slug)->firstOrFail();
        $date = Carbon::parse($request->date);
        $time = $request->time;

        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $time);
        $endTime = $startTime->copy()->addMinutes($bookingPage->duration);

        $booking = new Booking();
        $booking->booking_page_id = $bookingPage->id;
        $booking->name = $request->name;
        $booking->email = $request->email;
        $booking->starts_at = $startTime;
        $booking->ends_at = $endTime;
        $booking->notes = $request->notes;
        $booking->status = 'confirmed';
        $booking->uid = Str::uuid();
        $booking->save();

        $calendar = $bookingPage->getEffectiveDestinationCalendar();

        if (!$calendar) {
            Log::error('No destination calendar found for booking', [
                'booking_id' => $booking->id,
                'booking_page_id' => $bookingPage->id,
                'user_id' => $bookingPage->user_id,
            ]);

            return response()->json([
                'error' => 'No destination calendar found. Please check your calendar settings.'
            ], 500);
        }

        $googleAccount = $calendar->googleAccount;

        try {
            $googleCalendarService = new GoogleCalendarService($googleAccount);

            $eventDetails = [
                'summary' => "Meeting with {$request->name}",
                'description' => $request->notes ?? '',
                'location' => '',
                'start' => $startTime->toRfc3339String(),
                'end' => $endTime->toRfc3339String(),
                'attendees' => [
                    ['email' => $request->email, 'name' => $request->name],
                    ['email' => $googleAccount->email, 'name' => $googleAccount->name],
                ],
                'conferenceData' => $bookingPage->include_meet ? [
                    'createRequest' => ['requestId' => Str::uuid()->toString()],
                ] : null,
            ];

            $googleEvent = $googleCalendarService->createEvent($calendar->google_id, $eventDetails);

            $meetLink = null;
            if ($bookingPage->include_meet && $googleEvent->getConferenceData()) {
                $conferenceData = $googleEvent->getConferenceData();
                if ($conferenceData->getEntryPoints()) {
                    foreach ($conferenceData->getEntryPoints() as $entryPoint) {
                        if ($entryPoint->getEntryPointType() === 'video') {
                            $meetLink = $entryPoint->getUri();
                            break;
                        }
                    }
                }
            }

            $booking->google_event_id = $googleEvent->getId();
            $booking->calendar_id = $calendar->id;
            $booking->meeting_link = $meetLink;
            $booking->save();

            try {
                $googleCalendarService->syncEvents($calendar);

                if (!empty($bookingPage->selected_calendars)) {
                    foreach ($bookingPage->selected_calendars as $calendarId) {
                        if ($calendarId != $calendar->id) {
                            $otherCalendar = Calendar::find($calendarId);
                            if ($otherCalendar && $otherCalendar->googleAccount->is_active) {
                                $otherService = new GoogleCalendarService($otherCalendar->googleAccount);
                                $otherService->syncEvents($otherCalendar);
                            }
                        }
                    }
                }

                Log::info('Calendars synced after booking creation', [
                    'booking_id' => $booking->id,
                    'primary_calendar_id' => $calendar->id
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to sync calendars after booking, but booking was created', [
                    'message' => $e->getMessage(),
                    'booking_id' => $booking->id
                ]);
            }

            try {
                Mail::to($booking->email)
                    ->send(new BookingConfirmation($booking, false));

                Mail::to($bookingPage->user->email)
                    ->send(new BookingConfirmation($booking, true));

                Log::info('Booking notifications sent', [
                    'booking_id' => $booking->id,
                    'attendee' => $booking->email,
                    'organizer' => $bookingPage->user->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send booking notifications: ' . $e->getMessage(), [
                    'booking_id' => $booking->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to create calendar event: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'calendar_id' => $calendar->id,
            ]);

            return response()->json([
                'error' => 'Failed to create calendar event. Please try again later.'
            ], 500);
        }

        Log::info('Booking created successfully', [
            'booking_id' => $booking->id,
            'is_ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
            'accept' => $request->header('Accept'),
        ]);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'booking' => [
                    'name' => $booking->name,
                    'starts_at' => $booking->starts_at->format('l, F j, Y g:i A'),
                    'ends_at' => $booking->ends_at->format('g:i A'),
                    'meeting_link' => $booking->meeting_link,
                    'with' => $bookingPage->user->name,
                    'uid' => $booking->uid,
                ],
            ]);
        }

        return Inertia::render('Booking/Confirmation', [
            'booking' => [
                'name' => $booking->name,
                'starts_at' => $booking->starts_at->format('l, F j, Y g:i A'),
                'ends_at' => $booking->ends_at->format('g:i A'),
                'meeting_link' => $booking->meeting_link,
                'with' => $bookingPage->user->name,
                'uid' => $booking->uid,
            ],
        ]);
    }

    public function cancelBookingPage($uid)
    {
        $booking = Booking::with('bookingPage.user')
            ->where('uid', $uid)
            ->where('status', 'confirmed')
            ->firstOrFail();

        return Inertia::render('Booking/Cancel', [
            'booking' => [
                'id' => $booking->id,
                'uid' => $booking->uid,
                'name' => $booking->name,
                'starts_at' => $booking->starts_at->format('l, F j, Y g:i A'),
                'ends_at' => $booking->ends_at->format('g:i A'),
                'with' => $booking->bookingPage->user->name,
                'status' => $booking->status
            ],
        ]);
    }

    public function cancelBooking(Request $request, $uid)
    {
        $booking = Booking::where('uid', $uid)
            ->where('status', 'confirmed')
            ->firstOrFail();

        $booking->status = 'cancelled';
        $booking->save();

        if ($booking->google_event_id && $booking->calendar_id) {
            try {
                $calendar = Calendar::findOrFail($booking->calendar_id);
                $googleCalendarService = new GoogleCalendarService($calendar->googleAccount);
                $googleCalendarService->deleteEvent($calendar->google_id, $booking->google_event_id);

                Log::info('Google Calendar event deleted', [
                    'booking_id' => $booking->id,
                    'google_event_id' => $booking->google_event_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to delete calendar event: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'google_event_id' => $booking->google_event_id,
                ]);
            }
        }

        try {
            Mail::to($booking->email)
                ->send(new BookingCancellation($booking, false));

            Mail::to($booking->bookingPage->user->email)
                ->send(new BookingCancellation($booking, true));

            Log::info('Booking cancellation notifications sent', [
                'booking_id' => $booking->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send cancellation notifications: ' . $e->getMessage(), [
                'booking_id' => $booking->id
            ]);
        }

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your booking has been cancelled successfully.'
            ]);
        }

        return Inertia::render('Booking/CancelConfirmation', [
            'booking' => [
                'name' => $booking->name,
                'starts_at' => $booking->starts_at->format('l, F j, Y g:i A'),
                'with' => $booking->bookingPage->user->name,
                'bookingPage' => [
                    'slug' => $booking->bookingPage->slug
                ]
            ],
        ]);
    }

    public function listBookings()
    {
        $user = auth()->user();
        $bookings = $user->bookings()
            ->with('bookingPage', 'calendar')
            ->orderBy('starts_at', 'desc')
            ->get();

        return Inertia::render('Booking/ListBookings', [
            'bookings' => $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'name' => $booking->name,
                    'email' => $booking->email,
                    'starts_at' => $booking->starts_at->toIso8601String(),
                    'ends_at' => $booking->ends_at->toIso8601String(),
                    'status' => $booking->status,
                    'meeting_link' => $booking->meeting_link,
                    'uid' => $booking->uid,
                    'booking_page' => [
                        'title' => $booking->bookingPage->title,
                    ],
                    'calendar' => $booking->calendar ? [
                        'name' => $booking->calendar->name,
                    ] : null,
                ];
            }),
        ]);
    }
}
