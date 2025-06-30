<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\EventSession;
use App\Models\EventSpeaker;
use App\Models\CallForSpeakers;
use App\Http\Requests\Community\CreateCommunityEventRequest;
use App\Http\Requests\Community\UpdateCommunityEventRequest;
use App\Models\GoogleAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommunityEventController extends Controller
{
    public function index(Community $community)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $events = CommunityEvent::where('community_id', $community->id)
            ->with(['callForSpeakers', 'sessions', 'speakers'])
            ->withCount(['sessions', 'speakers'])
            ->latest()
            ->paginate(10);

        return Inertia::render('CommunityEvents/Index', [
            'community' => $community,
            'events' => $events,
        ]);
    }

    public function create(Community $community)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $availableCfs = CallForSpeakers::where('community_id', $community->id)
            ->whereIn('status', ['open', 'closed'])
            ->get(['id', 'title', 'status']);

        return Inertia::render('CommunityEvents/Create', [
            'community' => $community,
            'availableCfs' => $availableCfs,
            'eventTypes' => [
                'webinar' => 'Webinar',
                'workshop' => 'Workshop',
                'study_jam' => 'Study Jam',
                'meetup' => 'Meetup',
                'conference' => 'Conference',
                'other' => 'Other',
            ],
        ]);
    }

    public function store(CreateCommunityEventRequest $request, Community $community)
    {
        \DB::beginTransaction();

        try {
            $event = CommunityEvent::create([
                'community_id' => $community->id,
                'call_for_speakers_id' => $request->call_for_speakers_id,
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
                'type' => $request->type,
                'starts_at' => $request->starts_at,
                'ends_at' => $request->ends_at,
                'location' => $request->location,
                'meeting_link' => $request->meeting_link,
                'meeting_platform' => $request->meeting_platform,
                'is_online' => $request->is_online,
                'is_recurring' => $request->is_recurring,
                'recurrence_settings' => $request->recurrence_settings,
                'max_attendees' => $request->max_attendees,
                'requires_approval' => $request->requires_approval,
                'status' => 'draft',
                'is_public' => $request->is_public,
                'speaker_requirements' => $request->speaker_requirements,
            ]);

            if ($request->boolean('create_calendar_event') && $community->calendar_email) {
                try {
                    $calendarEventData = $this->createCalendarEvent($event, $community, $request);

                    if ($request->meeting_platform === 'google_meet' && !empty($calendarEventData['hangoutLink'])) {
                        $event->update(['meeting_link' => $calendarEventData['hangoutLink']]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to create calendar event for community event', [
                        'event_id' => $event->id,
                        'community_id' => $community->id,
                        'error' => $e->getMessage()
                    ]);

                    session()->flash('warning', 'Event created successfully, but calendar event creation failed. You can create it manually later.');
                }
            }

            if ($request->has('sessions') && is_array($request->sessions)) {
                foreach ($request->sessions as $sessionData) {
                    EventSession::create([
                        'community_event_id' => $event->id,
                        'title' => $sessionData['title'],
                        'description' => $sessionData['description'] ?? null,
                        'starts_at' => $sessionData['starts_at'],
                        'ends_at' => $sessionData['ends_at'],
                        'max_speakers' => $sessionData['max_speakers'],
                        'allows_applications' => $sessionData['allows_applications'] ?? true,
                        'block_on_application' => $sessionData['block_on_application'] ?? true,
                        'location' => $sessionData['location'] ?? null,
                        'meeting_link' => $sessionData['meeting_link'] ?? null,
                        'requirements' => $sessionData['requirements'] ?? null,
                    ]);
                }
            }

            \DB::commit();

            $successMessage = 'Event created successfully!';
            if ($request->boolean('create_calendar_event')) {
                $successMessage .= ' Calendar event has been created and invites will be sent to speakers.';
            }

            return redirect()->route('communities.events.show', [$community, $event])
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Failed to create community event', [
                'community_id' => $community->id,
                'request_data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create event. Please try again.');
        }
    }

    protected function createCalendarEvent(CommunityEvent $event, Community $community, CreateCommunityEventRequest $request)
    {
        $googleAccount = $community->googleAccount;
        if (!$googleAccount) {
            $googleAccount = GoogleAccount::where('user_id', $community->user_id)
                ->where('email', $community->calendar_email)
                ->first();

            if (!$googleAccount) {
                $googleAccount = GoogleAccount::where('user_id', $community->user_id)
                    ->where('is_primary', true)
                    ->first();
            }
        }

        if (!$googleAccount) {
            throw new \Exception('No Google account found for calendar integration');
        }

        $calendarService = new \App\Services\GoogleCalendarService($googleAccount);

        $targetCalendar = $community->getEffectiveDestinationCalendar();
        if (!$targetCalendar) {
            throw new \Exception('No target calendar found for community');
        }

        $eventDetails = [
            'summary' => $event->title,
            'description' => $this->buildCalendarEventDescription($event, $community),
            'location' => $event->is_online ? 'Online' : $event->location,
            'start' => $event->starts_at->toRfc3339String(),
            'end' => $event->ends_at->toRfc3339String(),
            'attendees' => $this->buildAttendeesForCalendarEvent($event, $community),
        ];

        if ($request->meeting_platform === 'google_meet' && $request->boolean('auto_create_google_meet')) {
            $eventDetails['conferenceData'] = [
                'createRequest' => [
                    'requestId' => \Illuminate\Support\Str::uuid(),
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ]
                ]
            ];
        }

        $calendarEvent = $calendarService->createEvent($targetCalendar->google_id, $eventDetails);

        $event->update([
            'google_calendar_event_id' => $calendarEvent->getId(),
            'google_calendar_id' => $targetCalendar->google_id,
        ]);

        return [
            'id' => $calendarEvent->getId(),
            'htmlLink' => $calendarEvent->getHtmlLink(),
            'hangoutLink' => $calendarEvent->getHangoutLink(),
        ];
    }

    protected function buildCalendarEventDescription(CommunityEvent $event, Community $community): string
    {
        $description = [];

        $description[] = "📅 {$event->title}";
        $description[] = "🏢 {$community->name}";
        $description[] = "";

        if ($event->description) {
            $description[] = "📝 Description:";
            $description[] = $event->description;
            $description[] = "";
        }

        if ($event->meeting_link && $event->meeting_platform !== 'google_meet') {
            $platformName = $this->getMeetingPlatformName($event->meeting_platform);
            $description[] = "🔗 {$platformName} Meeting:";
            $description[] = $event->meeting_link;
            $description[] = "";
        }

        if ($event->speaker_requirements) {
            $description[] = "📋 Speaker Requirements:";
            $description[] = $event->speaker_requirements;
            $description[] = "";
        }

        $description[] = "🌐 Event Page: " . $event->public_url;
        $description[] = "🏠 Community: " . $community->public_url;

        return implode("\n", $description);
    }

    protected function buildAttendeesForCalendarEvent(CommunityEvent $event, Community $community): array
    {
        $attendees = [];

        $attendees[] = [
            'email' => $community->contact_email ?: $community->user->email,
            'displayName' => $community->user->name . ' (Organizer)',
            'organizer' => true,
        ];

        $confirmedSpeakers = $event->speakers()->where('status', 'confirmed')->get();
        foreach ($confirmedSpeakers as $speaker) {
            $attendees[] = [
                'email' => $speaker->email,
                'displayName' => $speaker->name . ' (Speaker)',
            ];
        }

        return $attendees;
    }

    protected function getMeetingPlatformName(string $platform): string
    {
        $platformNames = [
            'zoom' => 'Zoom',
            'teams' => 'Microsoft Teams',
            'webex' => 'Cisco Webex',
            'discord' => 'Discord',
            'custom' => 'Meeting Platform',
        ];

        return $platformNames[$platform] ?? 'Meeting Platform';
    }

    public function show(Community $community, CommunityEvent $event)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $event->load([
            'callForSpeakers',
            'sessions.speakers',
            'sessions.cfsApplications' => function($query) {
                $query->with('reviewer');
            },
            'speakers.eventSession',
        ]);

        return Inertia::render('CommunityEvents/Show', [
            'community' => $community,
            'event' => $event,
        ]);
    }

    public function edit(Community $community, CommunityEvent $event)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $event->load(['sessions', 'speakers']);

        $availableCfs = CallForSpeakers::where('community_id', $community->id)
            ->whereIn('status', ['open', 'closed'])
            ->get(['id', 'title', 'status']);

        return Inertia::render('CommunityEvents/Edit', [
            'community' => $community,
            'event' => $event,
            'availableCfs' => $availableCfs,
            'eventTypes' => [
                'webinar' => 'Webinar',
                'workshop' => 'Workshop',
                'study_jam' => 'Study Jam',
                'meetup' => 'Meetup',
                'conference' => 'Conference',
                'other' => 'Other',
            ],
        ]);
    }

    public function update(UpdateCommunityEventRequest $request, Community $community, CommunityEvent $event)
    {
        $event->update($request->only([
            'title', 'slug', 'description', 'type', 'starts_at', 'ends_at',
            'location', 'meeting_link', 'max_attendees', 'speaker_requirements',
            'is_online', 'requires_approval', 'status', 'is_public'
        ]));

        return redirect()->back()->with('success', 'Event updated successfully!');
    }

    public function destroy(Community $community, CommunityEvent $event)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $event->delete();

        return redirect()->route('communities.events.index', $community)
            ->with('success', 'Event deleted successfully!');
    }

    public function publicList($slug)
    {
        $community = Community::where('slug', $slug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->firstOrFail();

        $events = CommunityEvent::where('community_id', $community->id)
            ->public()
            ->with(['speakers' => function($query) {
                $query->confirmed()->featured();
            }])
            ->latest('starts_at')
            ->paginate(12);

        return Inertia::render('Public/EventListing', [
            'community' => $community,
            'events' => $events,
        ]);
    }

    public function publicShow($communitySlug, $eventSlug)
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
                    $query->confirmed()->ordered();
                },
                'speakers' => function($query) {
                    $query->confirmed()->ordered();
                }
            ])
            ->firstOrFail();

        return Inertia::render('Public/EventDetails', [
            'community' => $community,
            'event' => $event,
            'canApply' => $event->acceptsApplications(),
        ]);
    }

    public function addSpeaker(Request $request, Community $community, CommunityEvent $event)
    {
        if (!auth()->user()->canManageCommunity($community)) {
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
            'success' => true,
            'speaker' => $speaker->load('eventSession'),
        ]);
    }
}
