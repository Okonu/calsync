<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\GoogleAccount;
use Carbon\Carbon;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleCalendarService
{
    protected GoogleClient $client;
    protected GoogleCalendar $service;
    protected GoogleAccount $account;

    public function __construct(GoogleAccount $account)
    {
        $this->account = $account;
        $this->client = new GoogleClient();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->setAccessType('offline');

        $this->client->setAccessToken([
            'access_token' => $account->access_token,
            'refresh_token' => $account->refresh_token,
            'expires_in' => Carbon::now()->diffInSeconds($account->token_expires_at),
        ]);

        if ($this->client->isAccessTokenExpired()) {
            $this->refreshToken();
        }

        $this->service = new GoogleCalendar($this->client);
    }

    protected function refreshToken(): void
    {
        try {
            $newToken = $this->client->fetchAccessTokenWithRefreshToken(
                $this->account->refresh_token
            );

            if (isset($newToken['access_token'])) {
                $this->account->update([
                    'access_token' => $newToken['access_token'],
                    'token_expires_at' => Carbon::now()->addSeconds($newToken['expires_in'] ?? 3600),
                ]);
            } else {
                throw new Exception('No access token returned from Google');
            }
        } catch (Exception $e) {
            Log::error('Failed to refresh token: ' . $e->getMessage(), [
                'account_id' => $this->account->id,
                'email' => $this->account->email,
            ]);

            // $this->account->update(['is_active' => false]);

            throw $e;
        }
    }

    public function syncCalendars(): void
    {
        try {
            $calendarList = $this->service->calendarList->listCalendarList();

            foreach ($calendarList->getItems() as $googleCalendar) {
                $calendar = Calendar::updateOrCreate(
                    [
                        'google_account_id' => $this->account->id,
                        'google_id' => $googleCalendar->getId(),
                    ],
                    [
                        'name' => $googleCalendar->getSummary() ?? 'Unnamed Calendar',
                        'color' => $googleCalendar->getBackgroundColor() ?? $this->account->color,
                        'is_primary' => $googleCalendar->getPrimary() ?? false,
                    ]
                );

                $this->syncEvents($calendar);
            }
        } catch (Exception $e) {
            Log::error('Failed to sync calendars: ' . $e->getMessage(), [
                'account_id' => $this->account->id,
                'email' => $this->account->email,
            ]);

            throw $e;
        }
    }

    public function syncEvents(Calendar $calendar): void
    {
        try {
            $startDate = Carbon::now()->subDays(30);
            $endDate = Carbon::now()->addDays(90);

            $events = $this->service->events->listEvents(
                $calendar->google_id,
                [
                    'timeMin' => $startDate->toRfc3339String(),
                    'timeMax' => $endDate->toRfc3339String(),
                    'singleEvents' => true,
                    'orderBy' => 'startTime',
                    'maxResults' => 2500,
                    'timeZone' => 'UTC',
                ]
            );

            $processedEventIds = [];

            foreach ($events->getItems() as $googleEvent) {
                $start = $googleEvent->getStart();
                $end = $googleEvent->getEnd();

                if (!$start || !$end) {
                    continue;
                }

                $allDay = !$start->dateTime;

                $startDate = $allDay
                    ? Carbon::parse($start->date)
                    : Carbon::parse($start->dateTime);

                $endDate = $allDay
                    ? Carbon::parse($end->date)
                    : Carbon::parse($end->dateTime);

                $event = Event::updateOrCreate(
                    [
                        'calendar_id' => $calendar->id,
                        'google_id' => $googleEvent->getId(),
                    ],
                    [
                        'title' => $googleEvent->getSummary() ?? '(No Title)',
                        'description' => $googleEvent->getDescription(),
                        'location' => $googleEvent->getLocation(),
                        'starts_at' => $startDate,
                        'ends_at' => $endDate,
                        'all_day' => $allDay,
                        'status' => $googleEvent->getStatus() ?? 'confirmed',
                        'color' => $googleEvent->getColorId() ? $this->getColorForId($googleEvent->getColorId()) : null,
                        'attendees' => $googleEvent->getAttendees() ? json_encode($googleEvent->getAttendees()) : null,
                        'recurrence' => $googleEvent->getRecurrence() ? json_encode($googleEvent->getRecurrence()) : null,
                    ]
                );

                $processedEventIds[] = $event->id;
            }

            Event::where('calendar_id', $calendar->id)
                ->where('starts_at', '>=', $startDate)
                ->where('ends_at', '<=', $endDate)
                ->whereNotIn('id', $processedEventIds)
                ->delete();

        } catch (Exception $e) {
            Log::error('Failed to sync events: ' . $e->getMessage(), [
                'calendar_id' => $calendar->id,
                'google_id' => $calendar->google_id,
                'account_id' => $calendar->googleAccount->id,
            ]);

            throw $e;
        }
    }

    protected function getColorForId(?string $colorId): ?string
    {
        $colorMap = [
            '1' => '#7986cb', // Lavender
            '2' => '#33b679', // Sage
            '3' => '#8e24aa', // Grape
            '4' => '#e67c73', // Flamingo
            '5' => '#f6bf26', // Banana
            '6' => '#f4511e', // Tangerine
            '7' => '#039be5', // Peacock
            '8' => '#616161', // Graphite
            '9' => '#3f51b5', // Blueberry
            '10' => '#0b8043', // Basil
            '11' => '#d50000', // Tomato
        ];

        return $colorId && isset($colorMap[$colorId]) ? $colorMap[$colorId] : null;
    }

    public function createEvent(string $calendarId, array $eventDetails)
    {
        try {
            Log::debug('Creating event with calendar ID', ['calendar_id' => $calendarId]);

            $event = new \Google\Service\Calendar\Event();
            $event->setSummary($eventDetails['summary']);

            if (isset($eventDetails['description'])) {
                $event->setDescription($eventDetails['description']);
            }

            if (isset($eventDetails['location'])) {
                $event->setLocation($eventDetails['location']);
            }

            $start = new \Google\Service\Calendar\EventDateTime();
            $start->setDateTime($eventDetails['start']);
            $start->setTimeZone('UTC');
            $event->setStart($start);

            $end = new \Google\Service\Calendar\EventDateTime();
            $end->setDateTime($eventDetails['end']);
            $end->setTimeZone('UTC');
            $event->setEnd($end);

            if (!empty($eventDetails['attendees'])) {
                $attendeesArray = [];
                foreach ($eventDetails['attendees'] as $attendee) {
                    $attendeeObj = new \Google\Service\Calendar\EventAttendee();
                    $attendeeObj->setEmail($attendee['email']);
                    if (isset($attendee['name'])) {
                        $attendeeObj->setDisplayName($attendee['name']);
                    }
                    $attendeesArray[] = $attendeeObj;
                }
                $event->setAttendees($attendeesArray);
            }

            if ($calendarId === 'primary' || strpos($calendarId, '@gmail.com') !== false) {
                $actualCalendarId = 'primary';
                Log::debug('Using primary calendar instead of email address');
            } else {
                $actualCalendarId = $calendarId;
            }

            if (!empty($eventDetails['conferenceData'])) {
                $conferenceData = new \Google\Service\Calendar\ConferenceData();
                $conferenceRequest = new \Google\Service\Calendar\CreateConferenceRequest();
                $conferenceRequest->setRequestId($eventDetails['conferenceData']['createRequest']['requestId']);
                $conferenceData->setCreateRequest($conferenceRequest);
                $event->setConferenceData($conferenceData);
            }

            $result = $this->service->events->insert(
                $actualCalendarId,
                $event,
                ['conferenceDataVersion' => 1]
            );

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to create event: ' . json_encode($e->getMessage()), [
                'calendar_id' => $calendarId,
                'event_details' => $eventDetails,
            ]);

            throw $e;
        }
    }

    public function getTokenInfo()
    {
        try {
            $tokenInfo = $this->client->verifyIdToken();
            return $tokenInfo;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteEvent(string $calendarId, string $eventId)
    {
        try {
            $this->service->events->delete($calendarId, $eventId);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete event: ' . $e->getMessage(), [
                'calendar_id' => $calendarId,
                'event_id' => $eventId,
            ]);

            throw $e;
        }
    }
}
