<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGoogleCalendars;
use App\Mail\BookingCancellation;
use App\Models\Calendar;
use App\Models\GoogleAccount;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index');
    }

    public function updateAccountColor(Request $request, $id)
    {
        $request->validate([
            'color' => 'required|string',
        ]);

        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $account->update([
            'color' => $request->color,
        ]);

        return response()->json([
            'success' => true,
            'account' => $account,
        ]);
    }

    public function updateAccountStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $account->update([
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'success' => true,
            'account' => $account,
        ]);
    }

    public function syncAccount($id)
    {
        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        SyncGoogleCalendars::dispatch($account);

        return response()->json([
            'success' => true,
            'message' => 'Calendar sync initiated'
        ]);
    }

    public function deleteAccount($id)
    {
        $account = GoogleAccount::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        Calendar::where('google_account_id', $account->id)->delete();

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }

    public function deletionPreview(Request $request)
    {
        $user = $request->user();

        $upcomingBookingsWithEvents = 0;

        if ($user->bookingPage) {
            $upcomingBookingsWithEvents = $user->bookingPage->bookings()
                ->where('status', 'confirmed')
                ->where('starts_at', '>=', now())
                ->whereNotNull('google_event_id')
                ->count();
        }

        return response()->json([
            'google_accounts' => $user->googleAccounts()->count(),
            'microsoft_accounts' => $user->microsoftAccounts()->count(),
            'communities' => $user->communities()->count(),
            'upcoming_bookings_with_events' => $upcomingBookingsWithEvents,
        ]);
    }

    public function destroyAccount(Request $request)
    {
        $request->validate([
            'confirmation' => 'required|string',
            'cancel_calendar_events' => 'required|boolean',
        ]);

        if (strtoupper(trim($request->input('confirmation'))) !== 'DELETE') {
            return response()->json([
                'message' => 'Please type DELETE to confirm.',
            ], 422);
        }

        $user = $request->user();
        $cancelledCount = 0;

        if ($request->boolean('cancel_calendar_events') && $user->bookingPage) {
            $bookings = $user->bookingPage->bookings()
                ->where('status', 'confirmed')
                ->whereNotNull('google_event_id')
                ->whereNotNull('calendar_id')
                ->with('calendar.googleAccount')
                ->get();

            foreach ($bookings as $booking) {
                try {
                    $calendar = $booking->calendar;

                    if ($calendar && $calendar->googleAccount) {
                        (new GoogleCalendarService($calendar->googleAccount))
                            ->deleteEvent($calendar->google_id, $booking->google_event_id);
                    }

                    Mail::to($booking->email)->send(new BookingCancellation($booking, false));

                    $booking->update(['status' => 'cancelled']);

                    $cancelledCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to cancel booking event during account deletion: ' . $e->getMessage(), [
                        'booking_id' => $booking->id,
                        'user_id' => $user->id,
                    ]);
                }
            }
        }

        $user->delete();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'cancelled_events' => $cancelledCount,
        ]);
    }

    public function listApiTokens(Request $request)
    {
        $tokens = $request->user()->tokens()
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'created_at', 'last_used_at']);

        return response()->json(['tokens' => $tokens]);
    }

    public function createApiToken(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $token = $request->user()->createToken($request->name, ['booking-pages:book']);

        return response()->json([
            'token' => [
                'id' => $token->accessToken->id,
                'name' => $token->accessToken->name,
                'created_at' => $token->accessToken->created_at,
            ],
            'plain_text_token' => $token->plainTextToken,
        ], 201);
    }

    public function revokeApiToken(Request $request, $id)
    {
        $deleted = $request->user()->tokens()->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Key not found.'], 404);
        }

        return response()->json(['success' => true]);
    }
}
