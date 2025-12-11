<?php

namespace App\Services;

use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\EventSession;
use App\Models\EventSpeaker;
use App\Models\GoogleAccount;
use App\Models\Calendar;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CommunityCalendarService extends GoogleCalendarService
{
    protected Community $community;
    protected ?Calendar $communityCalendar = null;

    public function __construct(Community $community)
    {
        $this->community = $community;

        $communityAccount = $this->getOrCreateCommunityAccount();

        parent::__construct($communityAccount);

        $this->communityCalendar = $this->getOrCreateCommunityCalendar();
    }

    protected function getOrCreateCommunityAccount(): GoogleAccount
    {
        $existingAccount = GoogleAccount::where('community_id', $this->community->id)
            ->where('account_type', 'community')
            ->first();

        if ($existingAccount) {
            return $existingAccount;
        }

        return GoogleAccount::createCommunityAccount($this->community, []);
    }

    protected function getOrCreateCommunityCalendar(): Calendar
    {
        $existingCalendar = Calendar::where('google_account_id', $this->account->id)
            ->where('name', 'like', '%' . $this->community->name . '%')
            ->first();

        if ($existingCalendar) {
            return $existingCalendar;
        }

        try {
            $googleCalendar = new \Google\Service\Calendar\Calendar();
            $googleCalendar->setSummary($this->community->name . ' Events');
            $googleCalendar->setDescription('Events and sessions for ' . $this->community->name);
            $googleCalendar->setTimeZone($this->community->timezone ?? 'UTC');

            $createdCalendar = $this->service->calendars->insert($googleCalendar);

            return Calendar::create([
                'google_account_id' => $this->account->id,
                'google_id' => $createdCalendar->getId(),
                'name' => $this->community->name . ' Events',
                'color' => $this->community->color,
                'is_visible' => true,
                'is_primary' => false,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create community calendar', [
                'community_id' => $this->community->id,
                'error' => $e->getMessage()
            ]);

            return Calendar::where('google_account_id', $this->account->id)
                ->where('is_primary', true)
                ->first();
        }
    }

    public function syncCommunityEvents(): void
    {
        $events = CommunityEvent::where('community_id', $this->community->id)
            ->where('status', 'published')
            ->with(['sessions.speakers', 'speakers'])
            ->get();

        foreach ($events as $event) {
            $this->syncEventToCalendar($event);
        }
    }

    public function syncEventToCalendar(CommunityEvent $event): void
    {
        try {
            if ($event->sessions->isNotEmpty()) {
                foreach ($event->sessions as $session) {
                    $this->syncSessionToCalendar($session);
                }
            } else {
                $this->syncMainEventToCalendar($event);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync community event to calendar', [
                'event_id' => $event->id,
                'community_id' => $this->community->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function syncSessionToCalendar(EventSession $session): void
    {
        $event = $session->communityEvent;
        $speakers = $session->speakers()->confirmed()->get();

        $eventDetails = [
            'summary' => $session->title . ' - ' . $event->title,
            'description' => $this->buildSessionDescription($session, $event, $speakers),
            'location' => $session->location ?: $event->location,
            'start' => $session->starts_at->toRfc3339String(),
            'end' => $session->ends_at->toRfc3339String(),
            'attendees' => $this->buildAttendeesArray($speakers),
        ];

        if ($session->meeting_link ?: $event->meeting_link) {
            $eventDetails['conferenceData'] = [
                'entryPoints' => [[
                    'entryPointType' => 'video',
                    'uri' => $session->meeting_link ?: $event->meeting_link,
                    'label' => 'Join Meeting'
                ]]
            ];
        }

        $this->createEvent($this->communityCalendar->google_id, $eventDetails);
    }

    protected function syncMainEventToCalendar(CommunityEvent $event): void
    {
        $speakers = $event->speakers()->confirmed()->get();

        $eventDetails = [
            'summary' => $event->title,
            'description' => $this->buildEventDescription($event, $speakers),
            'location' => $event->location,
            'start' => $event->starts_at->toRfc3339String(),
            'end' => $event->ends_at->toRfc3339String(),
            'attendees' => $this->buildAttendeesArray($speakers),
        ];

        if ($event->meeting_link) {
            $eventDetails['conferenceData'] = [
                'entryPoints' => [[
                    'entryPointType' => 'video',
                    'uri' => $event->meeting_link,
                    'label' => 'Join Meeting'
                ]]
            ];
        }

        $this->createEvent($this->communityCalendar->google_id, $eventDetails);
    }

    protected function buildSessionDescription(EventSession $session, CommunityEvent $event, $speakers): string
    {
        $description = [];

        $description[] = "🎯 Session: " . $session->title;
        $description[] = "📅 Event: " . $event->title;
        $description[] = "🏢 Community: " . $this->community->name;

        if ($session->description) {
            $description[] = "";
            $description[] = "📝 Description:";
            $description[] = $session->description;
        }

        if ($speakers->isNotEmpty()) {
            $description[] = "";
            $description[] = "🎤 Speaker(s):";
            foreach ($speakers as $speaker) {
                $speakerLine = "• " . $speaker->name;
                if ($speaker->company) {
                    $speakerLine .= " (" . $speaker->company . ")";
                }
                if ($speaker->topic_title) {
                    $speakerLine .= " - " . $speaker->topic_title;
                }
                $description[] = $speakerLine;
            }
        }

        if ($session->requirements) {
            $description[] = "";
            $description[] = "📋 Requirements:";
            $description[] = $session->requirements;
        }

        $description[] = "";
        $description[] = "🔗 Event Page: " . $event->public_url;
        $description[] = "🌐 Community: " . $this->community->public_url;

        return implode("\n", $description);
    }

    protected function buildEventDescription(CommunityEvent $event, $speakers): string
    {
        $description = [];

        $description[] = "📅 " . $event->title;
        $description[] = "🏢 " . $this->community->name;

        if ($event->description) {
            $description[] = "";
            $description[] = "📝 Description:";
            $description[] = $event->description;
        }

        if ($speakers->isNotEmpty()) {
            $description[] = "";
            $description[] = "🎤 Speaker(s):";
            foreach ($speakers as $speaker) {
                $speakerLine = "• " . $speaker->name;
                if ($speaker->company) {
                    $speakerLine .= " (" . $speaker->company . ")";
                }
                if ($speaker->topic_title) {
                    $speakerLine .= " - " . $speaker->topic_title;
                }
                $description[] = $speakerLine;
            }
        }

        $description[] = "";
        $description[] = "🔗 Event Page: " . $event->public_url;
        $description[] = "🌐 Community: " . $this->community->public_url;

        return implode("\n", $description);
    }

    protected function buildAttendeesArray($speakers): array
    {
        $attendees = [];

        $attendees[] = [
            'email' => $this->community->contact_email,
            'displayName' => $this->community->user->name . ' (Organizer)',
            'organizer' => true,
        ];

        foreach ($speakers as $speaker) {
            $attendees[] = [
                'email' => $speaker->email,
                'displayName' => $speaker->name . ' (Speaker)',
            ];
        }

        return $attendees;
    }

    public function createEventFromCommunityEvent(CommunityEvent $event): void
    {
        $this->syncEventToCalendar($event);

        $this->syncEvents($this->communityCalendar);
    }

    public function getCommunityCalendar(): ?Calendar
    {
        return $this->communityCalendar;
    }
}
