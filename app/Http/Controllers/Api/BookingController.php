<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPage;
use App\Models\Calendar;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $bookings = $user->bookings()
            ->with(['bookingPage:id,title,slug', 'calendar:id,name'])
            ->orderBy('starts_at', 'desc')
            ->paginate(20);

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $booking = Booking::whereHas('bookingPage', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['bookingPage', 'calendar'])->findOrFail($id);

        return response()->json([
            'booking' => $booking,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $booking = Booking::whereHas('bookingPage', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        if ($booking->status === 'cancelled') {
            return response()->json([
                'message' => 'Booking is already cancelled'
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        // Delete from Google Calendar if exists
        if ($booking->google_event_id && $booking->calendar_id) {
            try {
                $calendar = Calendar::findOrFail($booking->calendar_id);
                $googleCalendarService = new GoogleCalendarService($calendar->googleAccount);
                $googleCalendarService->deleteEvent($calendar->google_id, $booking->google_event_id);
            } catch (\Exception $e) {
                Log::error('Failed to delete calendar event: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'google_event_id' => $booking->google_event_id,
                ]);
            }
        }

        return response()->json([
            'booking' => $booking->fresh(),
            'message' => 'Booking cancelled successfully',
        ]);
    }

    public function getSettings(Request $request)
    {
        $user = $request->user();
        $bookingPage = $user->bookingPage;

        if (!$bookingPage) {
            return response()->json([
                'booking_page' => null,
                'message' => 'No booking page configured',
            ]);
        }

        return response()->json([
            'booking_page' => $bookingPage->load(['calendar']),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:15|max:480',
            'calendar_id' => 'required|exists:calendars,id',
            'is_active' => 'boolean',
            'available_days' => 'array',
            'available_days.*' => 'integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'buffer_time' => 'integer|min:0|max:60',
            'max_days_in_advance' => 'integer|min:1|max:365',
        ]);

        $bookingPage = $user->bookingPage;

        if (!$bookingPage) {
            $bookingPage = new BookingPage();
            $bookingPage->user_id = $user->id;
            $bookingPage->slug = Str::slug($user->name . '-' . Str::random(6));
        }

        $bookingPage->fill($request->only([
            'title', 'description', 'duration', 'calendar_id', 'is_active',
            'available_days', 'start_time', 'end_time', 'buffer_time',
            'max_days_in_advance'
        ]));

        $bookingPage->save();

        return response()->json([
            'booking_page' => $bookingPage->fresh()->load('calendar'),
            'message' => 'Booking settings updated successfully',
        ]);
    }

    public function publicShow($slug)
    {
        $bookingPage = BookingPage::where('slug', $slug)
            ->where('is_active', true)
            ->with(['user:id,name', 'calendar:id,name'])
            ->firstOrFail();

        return response()->json([
            'booking_page' => $bookingPage,
        ]);
    }

    public function getAvailableSlots(Request $request, $slug)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $bookingPage = BookingPage::where('slug', $slug)
            ->where('is_active', true)
            ->with('calendar.googleAccount')
            ->firstOrFail();

        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 6 = Saturday

        // Check if this day is available
        if (!in_array($dayOfWeek, $bookingPage->available_days ?? [1,2,3,4,5])) {
            return response()->json([
                'slots' => [],
                'message' => 'No availability on this day',
            ]);
        }

        // Generate time slots
        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $bookingPage->start_time);
        $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $bookingPage->end_time);
        $duration = $bookingPage->duration;
        $bufferTime = $bookingPage->buffer_time ?? 0;

        $slots = [];
        $currentTime = $startTime->copy();

        while ($currentTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            // Check if slot is available (no existing bookings)
            $isAvailable = !Booking::where('booking_page_id', $bookingPage->id)
                ->where('status', '!=', 'cancelled')
                ->where(function($query) use ($currentTime, $slotEnd) {
                    $query->whereBetween('starts_at', [$currentTime, $slotEnd])
                          ->orWhereBetween('ends_at', [$currentTime, $slotEnd])
                          ->orWhere(function($q) use ($currentTime, $slotEnd) {
                              $q->where('starts_at', '<=', $currentTime)
                                ->where('ends_at', '>=', $slotEnd);
                          });
                })
                ->exists();

            if ($isAvailable && $currentTime->gte(now())) {
                $slots[] = [
                    'start_time' => $currentTime->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'start_datetime' => $currentTime->toISOString(),
                    'end_datetime' => $slotEnd->toISOString(),
                ];
            }

            $currentTime->addMinutes($duration + $bufferTime);
        }

        return response()->json([
            'slots' => $slots,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    public function createBooking(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'start_datetime' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        $bookingPage = BookingPage::where('slug', $slug)
            ->where('is_active', true)
            ->with('calendar.googleAccount')
            ->firstOrFail();

        $startTime = Carbon::parse($request->start_datetime);
        $endTime = $startTime->copy()->addMinutes($bookingPage->duration);

        // Check slot availability again
        $existingBooking = Booking::where('booking_page_id', $bookingPage->id)
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('starts_at', [$startTime, $endTime])
                      ->orWhereBetween('ends_at', [$startTime, $endTime])
                      ->orWhere(function($q) use ($startTime, $endTime) {
                          $q->where('starts_at', '<=', $startTime)
                            ->where('ends_at', '>=', $endTime);
                      });
            })
            ->exists();

        if ($existingBooking) {
            return response()->json([
                'message' => 'This time slot is no longer available'
            ], 422);
        }

        $booking = Booking::create([
            'booking_page_id' => $bookingPage->id,
            'calendar_id' => $bookingPage->calendar_id,
            'name' => $request->name,
            'email' => $request->email,
            'starts_at' => $startTime,
            'ends_at' => $endTime,
            'notes' => $request->notes,
            'status' => 'confirmed',
            'uid' => Str::uuid(),
        ]);

        // Create Google Calendar event
        if ($bookingPage->calendar && $bookingPage->calendar->googleAccount) {
            try {
                $googleCalendarService = new GoogleCalendarService($bookingPage->calendar->googleAccount);

                $eventDetails = [
                    'summary' => $bookingPage->title . ' - ' . $request->name,
                    'description' => $request->notes,
                    'start' => $startTime->toRfc3339String(),
                    'end' => $endTime->toRfc3339String(),
                    'attendees' => [
                        [
                            'email' => $request->email,
                            'name' => $request->name,
                        ]
                    ],
                ];

                $googleEvent = $googleCalendarService->createEvent(
                    $bookingPage->calendar->google_id,
                    $eventDetails
                );

                $booking->update([
                    'google_event_id' => $googleEvent->getId(),
                    'meeting_link' => $googleEvent->getHangoutLink(),
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to create calendar event for booking: ' . $e->getMessage());
            }
        }

        return response()->json([
            'booking' => $booking->fresh(),
            'message' => 'Booking created successfully',
        ], 201);
    }
}