<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\EventSession;
use App\Models\EventSpeaker;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $events = $community->events()
            ->with(['callForSpeakers:id,title,status'])
            ->withCount(['sessions', 'speakers'])
            ->orderBy('starts_at', 'desc')
            ->paginate(20);

        return response()->json([
            'events' => $events,
        ]);
    }

    public function store(Request $request, Community $community)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:webinar,workshop,study_jam,meetup,conference,other',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'meeting_platform' => 'nullable|string|in:google_meet,zoom,teams,webex,discord,custom',
            'is_online' => 'boolean',
            'is_recurring' => 'boolean',
            'max_attendees' => 'nullable|integer|min:1',
            'requires_approval' => 'boolean',
            'is_public' => 'boolean',
            'speaker_requirements' => 'nullable|string',
            'call_for_speakers_id' => 'nullable|exists:call_for_speakers,id',
        ]);

        $event = CommunityEvent::create([
            'community_id' => $community->id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'meeting_platform' => $request->meeting_platform,
            'is_online' => $request->boolean('is_online'),
            'is_recurring' => $request->boolean('is_recurring'),
            'max_attendees' => $request->max_attendees,
            'requires_approval' => $request->boolean('requires_approval'),
            'status' => 'draft',
            'is_public' => $request->boolean('is_public'),
            'speaker_requirements' => $request->speaker_requirements,
            'call_for_speakers_id' => $request->call_for_speakers_id,
        ]);

        return response()->json([
            'event' => $event->fresh(),
            'message' => 'Event created successfully',
        ], 201);
    }

    public function show(Request $request, Community $community, CommunityEvent $event)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $event->load([
            'callForSpeakers:id,title,status',
            'sessions.speakers',
            'speakers.eventSession',
        ]);

        return response()->json([
            'event' => $event,
        ]);
    }

    public function update(Request $request, Community $community, CommunityEvent $event)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|string|in:webinar,workshop,study_jam,meetup,conference,other',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after:starts_at',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'meeting_platform' => 'nullable|string|in:google_meet,zoom,teams,webex,discord,custom',
            'is_online' => 'sometimes|boolean',
            'max_attendees' => 'nullable|integer|min:1',
            'requires_approval' => 'sometimes|boolean',
            'status' => 'sometimes|string|in:draft,published,cancelled',
            'is_public' => 'sometimes|boolean',
            'speaker_requirements' => 'nullable|string',
        ]);

        $event->update($request->only([
            'title', 'description', 'type', 'starts_at', 'ends_at',
            'location', 'meeting_link', 'meeting_platform', 'is_online',
            'max_attendees', 'requires_approval', 'status', 'is_public',
            'speaker_requirements'
        ]));

        return response()->json([
            'event' => $event->fresh(),
            'message' => 'Event updated successfully',
        ]);
    }

    public function destroy(Request $request, Community $community, CommunityEvent $event)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully',
        ]);
    }

    public function addSpeaker(Request $request, Community $community, CommunityEvent $event)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'company' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'topic_title' => 'nullable|string|max:200',
            'topic_description' => 'nullable|string|max:1000',
            'event_session_id' => 'nullable|exists:event_sessions,id',
            'is_featured' => 'boolean',
        ]);

        $speaker = EventSpeaker::create([
            'community_event_id' => $event->id,
            'event_session_id' => $request->event_session_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'company' => $request->company,
            'job_title' => $request->job_title,
            'topic_title' => $request->topic_title,
            'topic_description' => $request->topic_description,
            'assignment_type' => 'manual',
            'status' => 'confirmed',
            'is_featured' => $request->boolean('is_featured'),
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'speaker' => $speaker->load('eventSession'),
            'message' => 'Speaker added successfully',
        ], 201);
    }

    public function updateSpeaker(Request $request, Community $community, CommunityEvent $event, EventSpeaker $speaker)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:100',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'company' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'topic_title' => 'nullable|string|max:200',
            'topic_description' => 'nullable|string|max:1000',
            'event_session_id' => 'nullable|exists:event_sessions,id',
            'is_featured' => 'sometimes|boolean',
            'status' => 'sometimes|string|in:pending,confirmed,declined',
        ]);

        $speaker->update($request->only([
            'name', 'email', 'phone', 'bio', 'company', 'job_title',
            'topic_title', 'topic_description', 'event_session_id',
            'is_featured', 'status'
        ]));

        return response()->json([
            'speaker' => $speaker->fresh(),
            'message' => 'Speaker updated successfully',
        ]);
    }

    public function removeSpeaker(Request $request, Community $community, CommunityEvent $event, EventSpeaker $speaker)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $speaker->delete();

        return response()->json([
            'message' => 'Speaker removed successfully',
        ]);
    }

    // Event Sessions
    public function getSessions(Request $request, Community $community, CommunityEvent $event)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $sessions = $event->sessions()
            ->withCount(['speakers', 'cfsApplications'])
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function createSession(Request $request, Community $community, CommunityEvent $event)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'max_speakers' => 'required|integer|min:1|max:10',
            'allows_applications' => 'boolean',
            'block_on_application' => 'boolean',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'requirements' => 'nullable|string',
        ]);

        $session = EventSession::create([
            'community_event_id' => $event->id,
            'title' => $request->title,
            'description' => $request->description,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'max_speakers' => $request->max_speakers,
            'allows_applications' => $request->boolean('allows_applications', true),
            'block_on_application' => $request->boolean('block_on_application', true),
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'requirements' => $request->requirements,
            'status' => 'available',
        ]);

        return response()->json([
            'session' => $session->fresh(),
            'message' => 'Session created successfully',
        ], 201);
    }

    public function updateSession(Request $request, Community $community, CommunityEvent $event, EventSession $session)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after:starts_at',
            'max_speakers' => 'sometimes|integer|min:1|max:10',
            'allows_applications' => 'sometimes|boolean',
            'block_on_application' => 'sometimes|boolean',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url',
            'requirements' => 'nullable|string',
            'status' => 'sometimes|string|in:available,closed,full',
        ]);

        $session->update($request->only([
            'title', 'description', 'starts_at', 'ends_at', 'max_speakers',
            'allows_applications', 'block_on_application', 'location',
            'meeting_link', 'requirements', 'status'
        ]));

        return response()->json([
            'session' => $session->fresh(),
            'message' => 'Session updated successfully',
        ]);
    }

    public function deleteSession(Request $request, Community $community, CommunityEvent $event, EventSession $session)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $session->delete();

        return response()->json([
            'message' => 'Session deleted successfully',
        ]);
    }

    public function addSessionSpeaker(Request $request, Community $community, CommunityEvent $event, EventSession $session)
    {
        if (!$request->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'speaker_id' => 'required|exists:event_speakers,id',
        ]);

        $speaker = EventSpeaker::findOrFail($request->speaker_id);
        $speaker->update(['event_session_id' => $session->id]);

        return response()->json([
            'speaker' => $speaker->fresh(),
            'message' => 'Speaker assigned to session',
        ]);
    }

    // Public endpoints
    public function publicEventsList($slug)
    {
        $community = Community::where('slug', $slug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->firstOrFail();

        $events = $community->events()
            ->public()
            ->with(['speakers' => function($query) {
                $query->where('status', 'confirmed')->where('is_featured', true);
            }])
            ->orderBy('starts_at', 'desc')
            ->paginate(12);

        return response()->json([
            'community' => $community,
            'events' => $events,
        ]);
    }

    public function publicEventShow($communitySlug, $eventSlug)
    {
        $community = Community::where('slug', $communitySlug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->firstOrFail();

        $event = CommunityEvent::where('community_id', $community->id)
            ->where('slug', $eventSlug)
            ->where('is_public', true)
            ->where('status', 'published')
            ->with([
                'sessions.speakers' => function($query) {
                    $query->where('status', 'confirmed')->orderBy('order');
                },
                'speakers' => function($query) {
                    $query->where('status', 'confirmed')->orderBy('order');
                },
                'callForSpeakers'
            ])
            ->firstOrFail();

        return response()->json([
            'community' => $community,
            'event' => $event,
            'can_apply' => $event->acceptsApplications(),
        ]);
    }
}