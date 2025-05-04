<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\GoogleAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    /**
     * Display the calendar interface.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        // Get all active Google accounts for the authenticated user
        $accounts = GoogleAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get();

        // If there are no active accounts, redirect to connect a Google account
        if ($accounts->isEmpty()) {
            return redirect()->route('google.connect.redirect')
                ->with('info', 'Please connect a Google account to view your calendars');
        }

        // Get all calendars from all accounts
        $calendars = Calendar::whereIn('google_account_id', $accounts->pluck('id'))
            ->with('googleAccount')
            ->get()
            ->map(function ($calendar) {
                return [
                    'id' => $calendar->id,
                    'name' => $calendar->name,
                    'color' => $calendar->color,
                    'is_visible' => $calendar->is_visible,
                    'account_name' => $calendar->googleAccount->name,
                    'account_email' => $calendar->googleAccount->email,
                    'google_account_id' => $calendar->google_account_id,
                ];
            });

        return Inertia::render('Calendar/Index', [
            'accounts' => $accounts,
            'calendars' => $calendars,
        ]);
    }

    /**
     * Get all Google accounts for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccounts()
    {
        $accounts = GoogleAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get(['id', 'name', 'email', 'color', 'is_primary']);

        return response()->json($accounts);
    }

    /**
     * Get all calendars for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalendars()
    {
        $accounts = GoogleAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->pluck('id');

        $calendars = Calendar::whereIn('google_account_id', $accounts)
            ->with('googleAccount:id,name,email')
            ->get();

        return response()->json($calendars);
    }

    /**
     * Get events based on date range and optional calendar filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'calendars' => 'sometimes|array',
            'accounts' => 'sometimes|array', // Add ability to filter by accounts
            'limit' => 'sometimes|integer|min:1',
        ]);

        $calendarIds = $request->input('calendars', []);
        $accountIds = $request->input('accounts', []);
        $limit = $request->input('limit');

        // Get all user accounts
        $userAccounts = GoogleAccount::where('user_id', auth()->id())
            ->where('is_active', true);

        // Filter by specific accounts if requested
        if (!empty($accountIds)) {
            $userAccounts->whereIn('id', $accountIds);
        }

        $userAccountIds = $userAccounts->pluck('id');

        // If no specific calendars selected, get all visible calendars from selected accounts
        if (empty($calendarIds)) {
            $calendarQuery = Calendar::whereIn('google_account_id', $userAccountIds)
                ->where('is_visible', true);

            $calendarIds = $calendarQuery->pluck('id')->toArray();
        } else {
            // Ensure the user only gets calendars they actually have access to
            $calendarQuery = Calendar::whereIn('id', $calendarIds)
                ->whereIn('google_account_id', $userAccountIds);

            $calendarIds = $calendarQuery->pluck('id')->toArray();
        }

        // Fetch events
        $query = Event::with(['calendar.googleAccount' => function($query) {
            $query->select('id', 'name', 'email', 'color');
        }])
            ->whereIn('calendar_id', $calendarIds)
            ->where('starts_at', '>=', $request->input('start'))
            ->where('ends_at', '<=', $request->input('end'))
            ->orderBy('starts_at');

        if ($limit) {
            $query->limit($limit);
        }

        $events = $query->get();

        if (!$limit) {
            $events = $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->starts_at->toIso8601String(),
                    'end' => $event->ends_at->toIso8601String(),
                    'allDay' => $event->all_day,
                    'backgroundColor' => $event->color ?? $event->calendar->color,
                    'borderColor' => $event->color ?? $event->calendar->color,
                    'textColor' => $this->getTextColor($event->color ?? $event->calendar->color),
                    'extendedProps' => [
                        'description' => $event->description,
                        'location' => $event->location,
                        'calendar' => $event->calendar->name,
                        'account' => $event->calendar->googleAccount->email,
                        'accountName' => $event->calendar->googleAccount->name,
                        'googleAccountId' => $event->calendar->google_account_id,
                    ],
                ];
            });
        }

        return response()->json($events);
    }

    /**
     * Get detailed information about a specific event.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventDetails($id)
    {
        // Ensure the user can only access events from their accounts
        $userAccountIds = GoogleAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->pluck('id');

        $userCalendarIds = Calendar::whereIn('google_account_id', $userAccountIds)
            ->pluck('id');

        $event = Event::with('calendar.googleAccount')
            ->whereIn('calendar_id', $userCalendarIds)
            ->findOrFail($id);

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'location' => $event->location,
            'starts_at' => $event->starts_at->toIso8601String(),
            'ends_at' => $event->ends_at->toIso8601String(),
            'all_day' => $event->all_day,
            'color' => $event->color ?? $event->calendar->color,
            'calendar' => [
                'id' => $event->calendar->id,
                'name' => $event->calendar->name,
                'color' => $event->calendar->color,
            ],
            'account' => [
                'id' => $event->calendar->googleAccount->id,
                'name' => $event->calendar->googleAccount->name,
                'email' => $event->calendar->googleAccount->email,
            ],
            'attendees' => $event->attendees,
            'status' => $event->status,
        ]);
    }

    /**
     * Update calendar visibility.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Calendar  $calendar
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCalendarVisibility(Request $request, Calendar $calendar)
    {
        $request->validate([
            'is_visible' => 'required|boolean',
        ]);

        $userAccountIds = GoogleAccount::where('user_id', auth()->id())->pluck('id');

        if (!$userAccountIds->contains($calendar->google_account_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $calendar->update([
            'is_visible' => $request->is_visible,
        ]);

        return response()->json([
            'success' => true,
            'calendar' => $calendar,
        ]);
    }

    /**
     * Update calendar color.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Calendar  $calendar
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCalendarColor(Request $request, Calendar $calendar)
    {
        $request->validate([
            'color' => 'required|string',
        ]);

        $userAccountIds = GoogleAccount::where('user_id', auth()->id())->pluck('id');

        if (!$userAccountIds->contains($calendar->google_account_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $calendar->update([
            'color' => $request->color,
        ]);

        return response()->json([
            'success' => true,
            'calendar' => $calendar,
        ]);
    }

    /**
     * Calculate a contrasting text color (black or white) based on background color.
     *
     * @param  string  $hexColor
     * @return string
     */
    private function getTextColor($hexColor)
    {
        $hex = ltrim($hexColor, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }
}
