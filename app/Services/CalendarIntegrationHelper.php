<?php

namespace App\Services;

use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\EventSession;
use App\Models\Calendar;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CalendarIntegrationHelper
{
    protected Community $community;
    protected ?GoogleCalendarService $calendarService = null;

    public function __construct(Community $community)
    {
        $this->community = $community;
        $this->initializeCalendarService();
    }

    protected function initializeCalendarService(): void
    {
        $account = $this->community->getCalendarAccount();

        if ($account && $account->is_active) {
            try {
                $this->calendarService = new GoogleCalendarService($account);
            } catch (\Exception $e) {
                Log::error('Failed to initialize calendar service for community', [
                    'community_id' => $this->community->id,
                    'account_id' => $account->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function isConfigured(): bool
    {
        return $this->calendarService !== null && $this->community->hasCalendarIntegration();
    }

    public function createEventInCalendar(CommunityEvent $event): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $calendar = $this->community->getEffectiveEventCalendar();

            if (!$calendar) {
                Log::warning('No calendar found for community event creation', [
                    'community_id' => $this->community->id,
                    'event_id' => $event->id
                ]);
                return null;
            }

            $eventDetails = $this->buildEventDetails($event);

            $googleEvent = $this->calendarService->createEvent(
                $calendar->google_id,
                $eventDetails
            );

            // Update the event with Google Calendar info
            $event->update([
                'google_calendar_event_id' => $googleEvent->getId(),
                'google_calendar_id' => $calendar->google_id,
            ]);

            // If it's a Google Meet event, update the meeting link
            if ($event->meeting_platform === 'google_meet' && $googleEvent->getHangoutLink()) {
                $event->update(['meeting_link' => $googleEvent->getHangoutLink()]);
            }

            Log::info('Successfully created calendar event for community event', [
                'community_id' => $this->community->id,
                'event_id' => $event->id,
                'google_event_id' => $googleEvent->getId()
            ]);

            return $googleEvent->getId();

        } catch (\Exception $e) {
            Log::error('Failed to create calendar event for community event', [
                'community_id' => $this->community->id,
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    public function createSessionInCalendar(EventSession $session): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $calendar = $this->community->getEffectiveEventCalendar();

            if (!$calendar) {
                return null;
            }

            $eventDetails = $this->buildSessionEventDetails($session);

            $googleEvent = $this->calendarService->createEvent(
                $calendar->google_id,
                $eventDetails
            );

            Log::info('Successfully created calendar event for session', [
                'community_id' => $this->community->id,
                'session_id' => $session->id,
                'google_event_id' => $googleEvent->getId()
            ]);

            return $googleEvent->getId();

        } catch (\Exception $e) {
            Log::error('Failed to create calendar event for session', [
                'community_id' => $this->community->id,
                'session_id' => $session->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    protected function buildEventDetails(CommunityEvent $event): array
    {
        $eventDetails = [
            'summary' => $event->title,
            'description' => $this->buildEventDescription($event),
            'location' => $event->is_online ? 'Online' : $event->location,
            'start' => $event->starts_at->toRfc3339String(),
            'end' => $event->ends_at->toRfc3339String(),
            'attendees' => $this->buildEventAttendees($event),
        ];

        // Add meeting link or Google Meet configuration
        if ($event->is_online) {
            if ($event->meeting_platform === 'google_meet') {
                $eventDetails['conferenceData'] = [
                    'createRequest' => [
                        'requestId' => \Illuminate\Support\Str::uuid(),
                        'conferenceSolutionKey' => [
                            'type' => 'hangoutsMeet'
                        ]
                    ]
                ];
            } elseif ($event->meeting_link) {
                $eventDetails['description'] .= "\n\n🔗 Meeting Link: " . $event->meeting_link;
            }
        }

        return $eventDetails;
    }

    protected function buildSessionEventDetails(EventSession $session): array
    {
        $event = $session->communityEvent;

        $eventDetails = [
            'summary' => $session->title . ' - ' . $event->title,
            'description' => $this->buildSessionDescription($session, $event),
            'location' => $session->location ?: ($event->is_online ? 'Online' : $event->location),
            'start' => $session->starts_at->toRfc3339String(),
            'end' => $session->ends_at->toRfc3339String(),
            'attendees' => $this->buildSessionAttendees($session),
        ];

        // Add meeting link
        $meetingLink = $session->meeting_link ?: $event->meeting_link;
        if ($meetingLink && $event->is_online) {
            if ($event->meeting_platform === 'google_meet') {
                $eventDetails['conferenceData'] = [
                    'createRequest' => [
                        'requestId' => \Illuminate\Support\Str::uuid(),
                        'conferenceSolutionKey' => [
                            'type' => 'hangoutsMeet'
                        ]
                    ]
                ];
            } else {
                $eventDetails['description'] .= "\n\n🔗 Meeting Link: " . $meetingLink;
            }
        }

        return $eventDetails;
    }

    protected function buildEventDescription(CommunityEvent $event): string
    {
        $description = [];

        $description[] = "📅 {$event->title}";
        $description[] = "🏢 {$this->community->name}";
        $description[] = "";

        if ($event->description) {
            $description[] = "📝 Description:";
            $description[] = $event->description;
            $description[] = "";
        }

        if ($event->speaker_requirements) {
            $description[] = "📋 Speaker Requirements:";
            $description[] = $event->speaker_requirements;
            $description[] = "";
        }

        $description[] = "🌐 Event Page: " . $event->public_url;
        $description[] = "🏠 Community: " . $this->community->public_url;

        return implode("\n", $description);
    }

    protected function buildSessionDescription(EventSession $session, CommunityEvent $event): string
    {
        $description = [];

        $description[] = "🎯 Session: {$session->title}";
        $description[] = "📅 Event: {$event->title}";
        $description[] = "🏢 Community: {$this->community->name}";
        $description[] = "";

        if ($session->description) {
            $description[] = "📝 Session Description:";
            $description[] = $session->description;
            $description[] = "";
        }

        if ($session->requirements) {
            $description[] = "📋 Requirements:";
            $description[] = $session->requirements;
            $description[] = "";
        }

        $description[] = "🌐 Event Page: " . $event->public_url;
        $description[] = "🏠 Community: " . $this->community->public_url;

        return implode("\n", $description);
    }

    protected function buildEventAttendees(CommunityEvent $event): array
    {
        $attendees = [];

        // Add community organizer
        $attendees[] = [
            'email' => $this->community->contact_email ?: $this->community->user->email,
            'displayName' => $this->community->user->name . ' (Organizer)',
            'organizer' => true,
        ];

        // Add confirmed speakers
        $speakers = $event->speakers()->where('status', 'confirmed')->get();
        foreach ($speakers as $speaker) {
            if ($speaker->email) {
                $attendees[] = [
                    'email' => $speaker->email,
                    'displayName' => $speaker->name . ' (Speaker)',
                ];
            }
        }

        return $attendees;
    }

    protected function buildSessionAttendees(EventSession $session): array
    {
        $attendees = [];

        // Add community organizer
        $attendees[] = [
            'email' => $this->community->contact_email ?: $this->community->user->email,
            'displayName' => $this->community->user->name . ' (Organizer)',
            'organizer' => true,
        ];

        // Add session speakers
        $speakers = $session->speakers()->where('status', 'confirmed')->get();
        foreach ($speakers as $speaker) {
            if ($speaker->email) {
                $attendees[] = [
                    'email' => $speaker->email,
                    'displayName' => $speaker->name . ' (Speaker)',
                ];
            }
        }

        return $attendees;
    }

    public function checkAvailability(Carbon $startTime, Carbon $endTime): bool
    {
        if (!$this->isConfigured()) {
            return true; // Assume available if no calendar integration
        }

        try {
            $availabilityCalendars = $this->community->getAvailabilityCalendars();

            foreach ($availabilityCalendars as $calendar) {
                if (!$this->isTimeSlotAvailable($calendar, $startTime, $endTime)) {
                    return false;
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to check availability for community', [
                'community_id' => $this->community->id,
                'start_time' => $startTime->toISOString(),
                'end_time' => $endTime->toISOString(),
                'error' => $e->getMessage()
            ]);

            return true; // Assume available on error
        }
    }

    protected function isTimeSlotAvailable(Calendar $calendar, Carbon $startTime, Carbon $endTime): bool
    {
        // Check if there are any events in the calendar during this time
        $conflictingEvents = \App\Models\Event::where('calendar_id', $calendar->id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('starts_at', [$startTime, $endTime])
                    ->orWhereBetween('ends_at', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('starts_at', '<=', $startTime)
                            ->where('ends_at', '>=', $endTime);
                    });
            })
            ->exists();

        return !$conflictingEvents;
    }
}
