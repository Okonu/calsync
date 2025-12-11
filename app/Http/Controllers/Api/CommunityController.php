<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallForSpeakers;
use App\Models\CfsApplication;
use App\Models\Community;
use App\Models\CommunityEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $communities = $user->communities()
            ->where('is_active', true)
            ->withCount(['events', 'callsForSpeakers'])
            ->with('googleAccount:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'communities' => $communities,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'contact_email' => 'nullable|email',
            'timezone' => 'required|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_public' => 'boolean',
            'google_account_id' => 'nullable|exists:google_accounts,id',
            'destination_calendar_id' => 'nullable|exists:calendars,id',
        ]);

        $user = $request->user();

        $community = Community::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'website' => $request->website,
            'contact_email' => $request->contact_email,
            'timezone' => $request->timezone,
            'color' => $request->color ?? '#3B82F6',
            'is_public' => $request->boolean('is_public', true),
            'is_active' => true,
            'google_account_id' => $request->google_account_id,
            'destination_calendar_id' => $request->destination_calendar_id,
        ]);

        return response()->json([
            'community' => $community->fresh()->load(['googleAccount', 'destinationCalendar']),
            'message' => 'Community created successfully',
        ], 201);
    }

    public function show(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $community->load([
            'googleAccount:id,name,email',
            'destinationCalendar:id,name',
            'events' => function($query) {
                $query->latest()->limit(5);
            },
            'callsForSpeakers' => function($query) {
                $query->latest()->limit(5);
            }
        ]);

        return response()->json([
            'community' => $community,
        ]);
    }

    public function update(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'contact_email' => 'nullable|email',
            'timezone' => 'sometimes|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_public' => 'sometimes|boolean',
            'google_account_id' => 'nullable|exists:google_accounts,id',
            'destination_calendar_id' => 'nullable|exists:calendars,id',
        ]);

        $community->update($request->only([
            'name', 'description', 'website', 'contact_email', 'timezone',
            'color', 'is_public', 'google_account_id', 'destination_calendar_id'
        ]));

        return response()->json([
            'community' => $community->fresh()->load(['googleAccount', 'destinationCalendar']),
            'message' => 'Community updated successfully',
        ]);
    }

    public function destroy(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $community->update(['is_active' => false]);

        return response()->json([
            'message' => 'Community deactivated successfully',
        ]);
    }

    public function getStats(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $stats = [
            'events_count' => $community->events()->count(),
            'upcoming_events' => $community->events()
                ->where('starts_at', '>', now())
                ->where('status', 'published')
                ->count(),
            'cfs_count' => $community->callsForSpeakers()->count(),
            'active_cfs' => $community->callsForSpeakers()
                ->where('status', 'open')
                ->count(),
            'total_applications' => $community->callsForSpeakers()
                ->withCount('applications')
                ->get()
                ->sum('applications_count'),
            'pending_applications' => CfsApplication::whereHas('callForSpeakers', function($query) use ($community) {
                $query->where('community_id', $community->id);
            })->where('status', 'pending')->count(),
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function getCalendar(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $events = $community->events()
            ->whereBetween('starts_at', [$start, $end])
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->starts_at->toISOString(),
                    'end' => $event->ends_at->toISOString(),
                    'type' => $event->type,
                    'status' => $event->status,
                    'is_online' => $event->is_online,
                    'url' => $event->public_url,
                ];
            });

        return response()->json([
            'events' => $events,
        ]);
    }

    // Public endpoints
    public function publicIndex(Request $request)
    {
        $communities = Community::public()
            ->withCount('events')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'communities' => $communities,
        ]);
    }

    public function publicShow($slug)
    {
        $community = Community::where('slug', $slug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->withCount(['events', 'callsForSpeakers'])
            ->firstOrFail();

        $upcomingEvents = $community->events()
            ->public()
            ->upcoming()
            ->limit(5)
            ->get();

        $activeCfs = $community->callsForSpeakers()
            ->where('status', 'open')
            ->limit(3)
            ->get();

        return response()->json([
            'community' => $community,
            'upcoming_events' => $upcomingEvents,
            'active_cfs' => $activeCfs,
        ]);
    }

    public function publicCfsList($slug)
    {
        $community = Community::where('slug', $slug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->firstOrFail();

        $cfs = $community->callsForSpeakers()
            ->where('status', 'open')
            ->withCount('applications')
            ->orderBy('deadline', 'asc')
            ->paginate(10);

        return response()->json([
            'community' => $community,
            'cfs' => $cfs,
        ]);
    }

    public function publicCfsShow($communitySlug, $cfsSlug)
    {
        $community = Community::where('slug', $communitySlug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->firstOrFail();

        $cfs = CallForSpeakers::where('community_id', $community->id)
            ->where('slug', $cfsSlug)
            ->where('status', 'open')
            ->with('linkedEvent')
            ->firstOrFail();

        return response()->json([
            'community' => $community,
            'cfs' => $cfs,
        ]);
    }

    // CFS Management
    public function getCfsList(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $cfs = $community->callsForSpeakers()
            ->withCount('applications')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'cfs' => $cfs,
        ]);
    }

    public function createCfs(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date|after:now',
            'requirements' => 'nullable|string',
            'community_event_id' => 'nullable|exists:community_events,id',
        ]);

        $cfs = CallForSpeakers::create([
            'community_id' => $community->id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'deadline' => $request->deadline,
            'requirements' => $request->requirements,
            'status' => 'open',
            'community_event_id' => $request->community_event_id,
        ]);

        return response()->json([
            'cfs' => $cfs->fresh(),
            'message' => 'Call for speakers created successfully',
        ], 201);
    }

    public function getCfs(Request $request, Community $community, CallForSpeakers $cfs)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $cfs->load(['linkedEvent', 'applications' => function($query) {
            $query->with('reviewer')->latest();
        }]);

        return response()->json([
            'cfs' => $cfs,
        ]);
    }

    public function updateCfs(Request $request, Community $community, CallForSpeakers $cfs)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'deadline' => 'sometimes|date',
            'requirements' => 'nullable|string',
            'community_event_id' => 'nullable|exists:community_events,id',
        ]);

        $cfs->update($request->only([
            'title', 'description', 'deadline', 'requirements', 'community_event_id'
        ]));

        return response()->json([
            'cfs' => $cfs->fresh(),
            'message' => 'Call for speakers updated successfully',
        ]);
    }

    public function deleteCfs(Request $request, Community $community, CallForSpeakers $cfs)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $cfs->delete();

        return response()->json([
            'message' => 'Call for speakers deleted successfully',
        ]);
    }

    public function updateCfsStatus(Request $request, Community $community, CallForSpeakers $cfs)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:open,closed,archived',
        ]);

        $cfs->update(['status' => $request->status]);

        return response()->json([
            'cfs' => $cfs->fresh(),
            'message' => 'Status updated successfully',
        ]);
    }

    public function getCfsApplications(Request $request, Community $community, CallForSpeakers $cfs)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $applications = $cfs->applications()
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'applications' => $applications,
        ]);
    }

    public function approveCfsApplication(Request $request, Community $community, CallForSpeakers $cfs, CfsApplication $application)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $application->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewer_id' => $request->user()->id,
        ]);

        return response()->json([
            'application' => $application->fresh(),
            'message' => 'Application approved successfully',
        ]);
    }

    public function rejectCfsApplication(Request $request, Community $community, CallForSpeakers $cfs, CfsApplication $application)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'rejection_reason' => 'nullable|string',
        ]);

        $application->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewer_id' => $request->user()->id,
            'notes' => $request->rejection_reason,
        ]);

        return response()->json([
            'application' => $application->fresh(),
            'message' => 'Application rejected',
        ]);
    }
}