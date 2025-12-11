<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\GoogleAccount;
use App\Models\MicrosoftAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index()
    {
        $googleAccounts = GoogleAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get();

        $microsoftAccounts = MicrosoftAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get();

        $accounts = $googleAccounts->concat($microsoftAccounts);

        if ($accounts->isEmpty()) {
            return redirect()->route('google.connect.redirect')
                ->with('info', 'Please connect a calendar account to view your calendars');
        }

        $calendars = Calendar::where(function ($query) use ($googleAccounts, $microsoftAccounts) {
            $query->whereIn('google_account_id', $googleAccounts->pluck('id'))
                ->orWhereIn('microsoft_account_id', $microsoftAccounts->pluck('id'));
        })
            ->with(['googleAccount', 'microsoftAccount'])
            ->get()
            ->map(function ($calendar) {
                $account = $calendar->googleAccount ?? $calendar->microsoftAccount;
                return [
                    'id' => $calendar->id,
                    'name' => $calendar->name,
                    'color' => $calendar->color,
                    'is_visible' => $calendar->is_visible,
                    'account_name' => $account->name,
                    'account_email' => $account->email,
                    'google_account_id' => $calendar->google_account_id,
                    'microsoft_account_id' => $calendar->microsoft_account_id,
                    'provider' => $calendar->google_account_id ? 'google' : 'microsoft',
                ];
            });

        return Inertia::render('Calendar/Index', [
            'accounts' => $accounts,
            'calendars' => $calendars,
        ]);
    }

    public function getAccounts()
    {
        $googleAccounts = GoogleAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get(['id', 'name', 'email', 'color', 'is_primary'])
            ->map(function ($account) {
                $account->provider = 'google';
                return $account;
            });

        $microsoftAccounts = MicrosoftAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get(['id', 'name', 'email', 'color', 'is_primary'])
            ->map(function ($account) {
                $account->provider = 'microsoft';
                return $account;
            });

        return response()->json($googleAccounts->concat($microsoftAccounts));
    }

    public function getCalendars()
    {
        $googleAccountIds = GoogleAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->pluck('id');

        $microsoftAccountIds = MicrosoftAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->pluck('id');

        $calendars = Calendar::where(function ($query) use ($googleAccountIds, $microsoftAccountIds) {
            $query->whereIn('google_account_id', $googleAccountIds)
                ->orWhereIn('microsoft_account_id', $microsoftAccountIds);
        })
            ->with(['googleAccount:id,name,email', 'microsoftAccount:id,name,email'])
            ->get();

        return response()->json($calendars);
    }

    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'calendars' => 'sometimes|array',
            'accounts' => 'sometimes|array',
            'limit' => 'sometimes|integer|min:1',
        ]);

        $calendarIds = $request->input('calendars', []);
        $accountIds = $request->input('accounts', []); // This might need to distinguish provider
        $limit = $request->input('limit');

        // TODO: Handle filtering by specific accounts if mixed providers are used. 
        // For now, assume we fetch for all active user accounts if not filtered.

        $userGoogleIds = GoogleAccount::where('user_id', auth()->id())->where('is_active', true)->pluck('id');
        $userMicrosoftIds = MicrosoftAccount::where('user_id', auth()->id())->where('is_active', true)->pluck('id');

        if (empty($calendarIds)) {
            $calendarQuery = Calendar::where(function ($q) use ($userGoogleIds, $userMicrosoftIds) {
                $q->whereIn('google_account_id', $userGoogleIds)
                    ->orWhereIn('microsoft_account_id', $userMicrosoftIds);
            })->where('is_visible', true);

            $calendarIds = $calendarQuery->pluck('id')->toArray();
        } else {
            // Verify ownership
            $calendarQuery = Calendar::whereIn('id', $calendarIds)
                ->where(function ($q) use ($userGoogleIds, $userMicrosoftIds) {
                    $q->whereIn('google_account_id', $userGoogleIds)
                        ->orWhereIn('microsoft_account_id', $userMicrosoftIds);
                });

            $calendarIds = $calendarQuery->pluck('id')->toArray();
        }

        $query = Event::with(['calendar.googleAccount', 'calendar.microsoftAccount'])
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
                $account = $event->calendar->googleAccount ?? $event->calendar->microsoftAccount;
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
                        'account' => $account->email ?? 'Unknown',
                        'accountName' => $account->name ?? 'Unknown',
                        'googleAccountId' => $event->calendar->google_account_id,
                        'microsoftAccountId' => $event->calendar->microsoft_account_id,
                        'provider' => $event->calendar->google_account_id ? 'google' : 'microsoft',
                    ],
                ];
            });
        }

        return response()->json($events);
    }

    public function eventDetails($id)
    {
        $userGoogleIds = GoogleAccount::where('user_id', auth()->id())->where('is_active', true)->pluck('id');
        $userMicrosoftIds = MicrosoftAccount::where('user_id', auth()->id())->where('is_active', true)->pluck('id');

        $userCalendarIds = Calendar::where(function ($q) use ($userGoogleIds, $userMicrosoftIds) {
            $q->whereIn('google_account_id', $userGoogleIds)
                ->orWhereIn('microsoft_account_id', $userMicrosoftIds);
        })->pluck('id');

        $event = Event::with(['calendar.googleAccount', 'calendar.microsoftAccount'])
            ->whereIn('calendar_id', $userCalendarIds)
            ->findOrFail($id);

        $account = $event->calendar->googleAccount ?? $event->calendar->microsoftAccount;

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
                'id' => $account->id,
                'name' => $account->name,
                'email' => $account->email,
                'provider' => $event->calendar->google_account_id ? 'google' : 'microsoft',
            ],
            'attendees' => $event->attendees,
            'status' => $event->status,
        ]);
    }

    public function updateCalendarVisibility(Request $request, Calendar $calendar)
    {
        $request->validate([
            'is_visible' => 'required|boolean',
        ]);

        $userGoogleIds = GoogleAccount::where('user_id', auth()->id())->pluck('id');
        $userMicrosoftIds = MicrosoftAccount::where('user_id', auth()->id())->pluck('id');

        $isOwned = false;
        if ($calendar->google_account_id && $userGoogleIds->contains($calendar->google_account_id)) {
            $isOwned = true;
        } elseif ($calendar->microsoft_account_id && $userMicrosoftIds->contains($calendar->microsoft_account_id)) {
            $isOwned = true;
        }

        if (!$isOwned) {
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

    public function updateCalendarColor(Request $request, Calendar $calendar)
    {
        $request->validate([
            'color' => 'required|string',
        ]);

        $userGoogleIds = GoogleAccount::where('user_id', auth()->id())->pluck('id');
        $userMicrosoftIds = MicrosoftAccount::where('user_id', auth()->id())->pluck('id');

        $isOwned = false;
        if ($calendar->google_account_id && $userGoogleIds->contains($calendar->google_account_id)) {
            $isOwned = true;
        } elseif ($calendar->microsoft_account_id && $userMicrosoftIds->contains($calendar->microsoft_account_id)) {
            $isOwned = true;
        }

        if (!$isOwned) {
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
