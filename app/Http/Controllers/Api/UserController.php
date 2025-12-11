<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user()->load([
            'googleAccounts' => function($query) {
                $query->where('is_active', true);
            },
            'communities' => function($query) {
                $query->where('is_active', true)->withCount(['events', 'callsForSpeakers']);
            },
            'bookingPage'
        ]);

        return response()->json([
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'password' => ['sometimes', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $updateData = $request->only(['name', 'email']);

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'user' => $user->fresh()->load(['googleAccounts', 'communities', 'bookingPage']),
            'message' => 'Profile updated successfully'
        ]);
    }

    public function getStats(Request $request)
    {
        $user = $request->user();

        $stats = [
            'communities_count' => $user->communities()->where('is_active', true)->count(),
            'events_count' => $user->communityEvents()->count(),
            'bookings_count' => $user->bookings()->count(),
            'upcoming_events' => $user->communityEvents()
                ->where('starts_at', '>', now())
                ->where('status', 'published')
                ->count(),
            'active_cfs' => $user->callsForSpeakers()
                ->where('status', 'open')
                ->count(),
            'total_applications' => $user->callsForSpeakers()
                ->withCount('applications')
                ->get()
                ->sum('applications_count'),
        ];

        // Recent activity
        $recentEvents = $user->communityEvents()
            ->with('community:id,name,slug')
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $recentBookings = $user->bookings()
            ->with(['bookingPage:id,title,slug', 'calendar:id,name'])
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_events' => $recentEvents,
            'recent_bookings' => $recentBookings,
        ]);
    }
}