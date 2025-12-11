<?php

namespace App\Http\Controllers;

use App\Http\Requests\Community\CreateCommunityRequest;
use App\Http\Requests\Community\UpdateCommunityRequest;
use App\Models\Calendar;
use App\Models\CallForSpeakers;
use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\GoogleAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CommunityController extends Controller
{
    public function index()
    {
        $communities = auth()->user()->activeCommunities()
            ->withCount(['events', 'callsForSpeakers'])
            ->latest()
            ->get();

        return Inertia::render('Community/Index', [
            'communities' => $communities,
        ]);
    }

    public function create()
    {
        if (!auth()->user()->hasCommunitiesFeature()) {
            return redirect()->route('dashboard')
                ->with('error', 'Communities feature not available for your account.');
        }

        return Inertia::render('Community/Create');
    }

    public function store(CreateCommunityRequest $request)
    {
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('community-logos', 'public');
        }

        $community = Community::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'website' => $request->website,
            'contact_email' => $request->contact_email,
            'timezone' => $request->timezone,
            'color' => $request->color,
            'logo' => $logoPath,
            'is_public' => $request->is_public,
            'social_links' => $request->social_links,
        ]);

        return redirect()->route('communities.dashboard', $community)
            ->with('success', 'Community created successfully!');
    }

    public function show($slug)
    {
        $community = Community::where('slug', $slug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->with(['user'])
            ->firstOrFail();

        $upcomingEvents = CommunityEvent::where('community_id', $community->id)
            ->public()
            ->upcoming()
            ->with([
                'community', // Add this line to load the community relationship
                'speakers' => function($query) {
                    $query->confirmed()->featured();
                }
            ])
            ->limit(6)
            ->get();

        $openCfs = CallForSpeakers::where('community_id', $community->id)
            ->open()
            ->public()
            ->with('community')
            ->limit(3)
            ->get();

        return Inertia::render('Public/CommunityProfile', [
            'community' => [
                'id' => $community->id,
                'name' => $community->name,
                'slug' => $community->slug,
                'description' => $community->description,
                'logo_url' => $community->logo_url,
                'website' => $community->website,
                'contact_email' => $community->contact_email,
                'social_links' => $community->social_links,
                'color' => $community->color,
                'owner' => [
                    'name' => $community->user->name,
                ],
            ],
            'upcomingEvents' => $upcomingEvents->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'slug' => $event->slug,
                    'description' => $event->description,
                    'type' => $event->type,
                    'starts_at' => $event->starts_at,
                    'ends_at' => $event->ends_at,
                    'location' => $event->location,
                    'is_online' => $event->is_online,
                    'total_speakers' => $event->total_speakers,
                    'public_url' => $event->public_url,
                ];
            }),
            'openCfs' => $openCfs->map(function ($cfs) {
                return [
                    'id' => $cfs->id,
                    'title' => $cfs->title,
                    'slug' => $cfs->slug,
                    'description' => $cfs->description,
                    'closes_at' => $cfs->closes_at,
                    'total_applications' => $cfs->show_application_count ? $cfs->total_applications : null,
                    'public_url' => $cfs->public_url,
                ];
            }),
        ]);
    }

    public function dashboard(Community $community)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403, 'You do not have permission to manage this community.');
        }

        $stats = [
            'total_events' => $community->events()->count(),
            'upcoming_events' => $community->events()->upcoming()->count(),
            'total_cfs' => $community->callsForSpeakers()->count(),
            'open_cfs' => $community->callsForSpeakers()->open()->count(),
            'total_speakers' => $community->speakers()->confirmed()->count(),
            'pending_applications' => $community->callsForSpeakers()
                ->withCount(['applications' => function($query) {
                    $query->where('status', 'pending');
                }])
                ->get()
                ->sum('applications_count'),
        ];

        $recentEvents = $community->events()
            ->with(['sessions', 'speakers' => function($query) {
                $query->confirmed();
            }])
            ->latest()
            ->limit(5)
            ->get();

        $recentCfs = $community->callsForSpeakers()
            ->withCount(['applications'])
            ->latest()
            ->limit(3)
            ->get();

        return Inertia::render('Community/Dashboard', [
            'community' => $community,
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'recentCfs' => $recentCfs,
        ]);
    }

    public function settings($communityIdentifier)
    {
        $community = Community::where('id', $communityIdentifier)
            ->orWhere('slug', $communityIdentifier)
            ->firstOrFail();

        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $accounts = GoogleAccount::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get(['id', 'name', 'email', 'color', 'is_primary']);

        $calendars = Calendar::whereIn('google_account_id', $accounts->pluck('id'))
            ->with('googleAccount:id,name,email')
            ->get();

        return Inertia::render('Community/Settings', [
            'community' => $community,
            'accounts' => $accounts,
            'calendars' => $calendars,
        ]);
    }

    public function update(UpdateCommunityRequest $request, $communityIdentifier)
    {
        $community = Community::where('id', $communityIdentifier)
            ->orWhere('slug', $communityIdentifier)
            ->firstOrFail();

        $updateData = $request->only([
            'name', 'slug', 'description', 'website', 'contact_email',
            'calendar_email', 'destination_calendar_id', 'availability_calendars',
            'timezone', 'color', 'social_links', 'is_public'
        ]);

        if ($request->calendar_email) {
            $googleAccount = GoogleAccount::where('user_id', auth()->id())
                ->where('email', $request->calendar_email)
                ->where('is_active', true)
                ->first();

            if ($googleAccount) {
                $updateData['google_account_id'] = $googleAccount->id;
            }
        } else {
            $updateData['google_account_id'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($community->logo) {
                Storage::disk('public')->delete($community->logo);
            }
            $updateData['logo'] = $request->file('logo')->store('community-logos', 'public');
        }

        $community->update($updateData);

        return redirect()->back()->with('success', 'Community updated successfully!');
    }

    public function destroy($communityIdentifier)
    {
        $community = Community::where('id', $communityIdentifier)
            ->orWhere('slug', $communityIdentifier)
            ->firstOrFail();

        if ($community->user_id !== auth()->id()) {
            abort(403);
        }

        if ($community->logo) {
            Storage::disk('public')->delete($community->logo);
        }

        $community->delete();

        return redirect()->route('communities.index')
            ->with('success', 'Community deleted successfully!');
    }

    public function getCalendar(Request $request, Community $community)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $events = CommunityEvent::where('community_id', $community->id)
            ->where('starts_at', '>=', $request->start)
            ->where('ends_at', '<=', $request->end)
            ->with(['sessions', 'speakers'])
            ->get();

        return response()->json($events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->starts_at->toIso8601String(),
                'end' => $event->ends_at->toIso8601String(),
                'backgroundColor' => $event->community->color,
                'borderColor' => $event->community->color,
                'extendedProps' => [
                    'type' => $event->type,
                    'status' => $event->status,
                    'sessions_count' => $event->sessions->count(),
                    'speakers_count' => $event->speakers->count(),
                    'url' => route('communities.events.show', [$event->community->slug, $event->slug]),
                ],
            ];
        }));
    }
}
