<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use App\Models\GoogleAccount;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $accounts = $user->googleAccounts()
            ->with(['calendars' => function($query) {
                $query->where('is_visible', true);
            }])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'accounts' => $accounts,
        ]);
    }

    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'calendar_ids' => 'sometimes|array',
            'calendar_ids.*' => 'integer|exists:calendars,id',
        ]);

        $user = $request->user();
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $calendarsQuery = Calendar::whereHas('googleAccount', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })->where('is_visible', true);

        if ($request->has('calendar_ids')) {
            $calendarsQuery->whereIn('id', $request->calendar_ids);
        }

        $calendars = $calendarsQuery->get();
        $events = collect();

        foreach ($calendars as $calendar) {
            $calendarEvents = $calendar->events()
                ->whereBetween('starts_at', [$start, $end])
                ->get()
                ->map(function($event) use ($calendar) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start' => $event->starts_at->toISOString(),
                        'end' => $event->ends_at->toISOString(),
                        'allDay' => $event->all_day,
                        'description' => $event->description,
                        'location' => $event->location,
                        'status' => $event->status,
                        'color' => $event->color ?: $calendar->color,
                        'calendar' => [
                            'id' => $calendar->id,
                            'name' => $calendar->name,
                            'color' => $calendar->color,
                        ],
                        'google_id' => $event->google_id,
                        'attendees' => $event->attendees ? json_decode($event->attendees) : null,
                    ];
                });

            $events = $events->merge($calendarEvents);
        }

        return response()->json([
            'events' => $events->sortBy('starts_at')->values(),
        ]);
    }

    public function eventDetails(Request $request, $id)
    {
        $user = $request->user();

        $event = \App\Models\Event::whereHas('calendar.googleAccount', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['calendar.googleAccount'])->findOrFail($id);

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at->toISOString(),
                'ends_at' => $event->ends_at->toISOString(),
                'all_day' => $event->all_day,
                'status' => $event->status,
                'color' => $event->color,
                'google_id' => $event->google_id,
                'attendees' => $event->attendees ? json_decode($event->attendees) : null,
                'recurrence' => $event->recurrence ? json_decode($event->recurrence) : null,
                'calendar' => [
                    'id' => $event->calendar->id,
                    'name' => $event->calendar->name,
                    'color' => $event->calendar->color,
                    'account' => [
                        'id' => $event->calendar->googleAccount->id,
                        'name' => $event->calendar->googleAccount->name,
                        'email' => $event->calendar->googleAccount->email,
                    ],
                ],
            ],
        ]);
    }

    public function getCalendars(Request $request)
    {
        $user = $request->user();
        $calendars = Calendar::whereHas('googleAccount', function($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })->with('googleAccount:id,name,email,color')->get();

        return response()->json([
            'calendars' => $calendars,
        ]);
    }

    public function updateCalendar(Request $request, Calendar $calendar)
    {
        $this->authorize('update', $calendar);

        $request->validate([
            'is_visible' => 'sometimes|boolean',
            'color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $calendar->update($request->only(['is_visible', 'color']));

        return response()->json([
            'calendar' => $calendar->fresh(),
            'message' => 'Calendar updated successfully',
        ]);
    }

    public function updateCalendarVisibility(Request $request, Calendar $calendar)
    {
        $this->authorize('update', $calendar);

        $request->validate([
            'is_visible' => 'required|boolean',
        ]);

        $calendar->update(['is_visible' => $request->is_visible]);

        return response()->json([
            'calendar' => $calendar->fresh(),
            'message' => 'Calendar visibility updated',
        ]);
    }

    public function updateCalendarColor(Request $request, Calendar $calendar)
    {
        $this->authorize('update', $calendar);

        $request->validate([
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $calendar->update(['color' => $request->color]);

        return response()->json([
            'calendar' => $calendar->fresh(),
            'message' => 'Calendar color updated',
        ]);
    }

    public function getAccounts(Request $request)
    {
        $user = $request->user();
        $accounts = $user->googleAccounts()
            ->withCount('calendars')
            ->orderBy('is_primary', 'desc')
            ->get();

        return response()->json([
            'accounts' => $accounts,
        ]);
    }

    public function syncAccount(Request $request, $id)
    {
        $user = $request->user();
        $account = GoogleAccount::where('user_id', $user->id)->findOrFail($id);

        try {
            $calendarService = new GoogleCalendarService($account);
            $calendarService->syncCalendars();

            return response()->json([
                'message' => 'Account synced successfully',
                'account' => $account->fresh()->load('calendars'),
            ]);
        } catch (\Exception $e) {
            Log::error('Account sync failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateAccountColor(Request $request, $id)
    {
        $user = $request->user();
        $account = GoogleAccount::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $account->update(['color' => $request->color]);

        return response()->json([
            'account' => $account->fresh(),
            'message' => 'Account color updated',
        ]);
    }

    public function updateAccountStatus(Request $request, $id)
    {
        $user = $request->user();
        $account = GoogleAccount::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $account->update(['is_active' => $request->is_active]);

        return response()->json([
            'account' => $account->fresh(),
            'message' => 'Account status updated',
        ]);
    }

    public function deleteAccount(Request $request, $id)
    {
        $user = $request->user();
        $account = GoogleAccount::where('user_id', $user->id)->findOrFail($id);

        if ($account->is_primary && $user->googleAccounts()->count() > 1) {
            return response()->json([
                'message' => 'Cannot delete primary account when other accounts exist',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }
}