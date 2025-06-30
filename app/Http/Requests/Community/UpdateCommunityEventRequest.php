<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunityEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $community = $this->route('community');
        return auth()->check() && auth()->user()->canManageCommunity($community);
    }

    public function rules(): array
    {
        $community = $this->route('community');
        $event = $this->route('event');

        return [
            'title' => 'required|string|max:200',
            'slug' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('community_events', 'slug')
                    ->ignore($event->id)
                    ->where('community_id', $community->id)
            ],
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:webinar,workshop,study_jam,meetup,conference,other',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url|max:500',
            'is_online' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_settings' => 'nullable|array',
            'recurrence_settings.frequency' => 'required_if:is_recurring,true|in:daily,weekly,monthly',
            'recurrence_settings.interval' => 'required_if:is_recurring,true|integer|min:1|max:12',
            'recurrence_settings.days_of_week' => 'required_if:recurrence_settings.frequency,weekly|array',
            'recurrence_settings.end_date' => 'nullable|date|after:starts_at',
            'max_attendees' => 'nullable|integer|min:1|max:10000',
            'requires_approval' => 'boolean',
            'status' => 'required|in:draft,published,cancelled,completed',
            'is_public' => 'boolean',
            'speaker_requirements' => 'nullable|string|max:1000',
            'call_for_speakers_id' => [
                'nullable',
                Rule::exists('calls_for_speakers', 'id')->where('community_id', $community->id)
            ],

            // Sessions (for events that support session updates)
            'sessions' => 'nullable|array|max:20',
            'sessions.*.id' => 'nullable|exists:event_sessions,id',
            'sessions.*.title' => 'required|string|max:200',
            'sessions.*.description' => 'nullable|string|max:1000',
            'sessions.*.starts_at' => 'required|date',
            'sessions.*.ends_at' => 'required|date|after:sessions.*.starts_at',
            'sessions.*.max_speakers' => 'required|integer|min:1|max:10',
            'sessions.*.allows_applications' => 'boolean',
            'sessions.*.block_on_application' => 'boolean',
            'sessions.*.location' => 'nullable|string|max:255',
            'sessions.*.meeting_link' => 'nullable|url|max:500',
            'sessions.*.requirements' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Event title is required.',
            'slug.unique' => 'This slug is already used by another event in this community.',
            'slug.alpha_dash' => 'Slug may only contain letters, numbers, dashes, and underscores.',
            'type.required' => 'Please select an event type.',
            'type.in' => 'Please select a valid event type.',
            'starts_at.date' => 'Please provide a valid start date and time.',
            'ends_at.after' => 'End time must be after start time.',
            'meeting_link.url' => 'Meeting link must be a valid URL.',
            'max_attendees.min' => 'Maximum attendees must be at least 1.',
            'max_attendees.max' => 'Maximum attendees cannot exceed 10,000.',
            'status.required' => 'Event status is required.',
            'status.in' => 'Please select a valid event status.',
            'call_for_speakers_id.exists' => 'Selected call for speakers does not exist in this community.',

            // Recurrence validation messages
            'recurrence_settings.frequency.required_if' => 'Recurrence frequency is required for recurring events.',
            'recurrence_settings.interval.required_if' => 'Recurrence interval is required for recurring events.',
            'recurrence_settings.interval.min' => 'Recurrence interval must be at least 1.',
            'recurrence_settings.interval.max' => 'Recurrence interval cannot exceed 12.',
            'recurrence_settings.days_of_week.required_if' => 'Days of week are required for weekly recurrence.',
            'recurrence_settings.end_date.after' => 'Recurrence end date must be after the event start date.',

            // Session validation messages
            'sessions.max' => 'You can have a maximum of 20 sessions per event.',
            'sessions.*.title.required' => 'Session title is required.',
            'sessions.*.starts_at.required' => 'Session start time is required.',
            'sessions.*.ends_at.required' => 'Session end time is required.',
            'sessions.*.ends_at.after' => 'Session end time must be after start time.',
            'sessions.*.max_speakers.required' => 'Maximum speakers per session is required.',
            'sessions.*.max_speakers.min' => 'Each session must allow at least 1 speaker.',
            'sessions.*.max_speakers.max' => 'Each session can have a maximum of 10 speakers.',
            'sessions.*.meeting_link.url' => 'Session meeting link must be a valid URL.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('slug') || empty($this->slug)) {
            if ($this->has('title')) {
                $this->merge([
                    'slug' => \Illuminate\Support\Str::slug($this->title)
                ]);
            }
        }

        $this->merge([
            'is_online' => $this->boolean('is_online', true),
            'is_recurring' => $this->boolean('is_recurring', false),
            'requires_approval' => $this->boolean('requires_approval', false),
            'is_public' => $this->boolean('is_public', true),
        ]);

        if (!$this->boolean('is_recurring')) {
            $this->merge(['recurrence_settings' => null]);
        }

        if ($this->has('sessions') && is_array($this->sessions)) {
            $cleanedSessions = [];
            foreach ($this->sessions as $index => $session) {
                if (!empty($session['title'])) {
                    $cleanedSessions[] = array_merge($session, [
                        'allows_applications' => $session['allows_applications'] ?? true,
                        'block_on_application' => $session['block_on_application'] ?? true,
                    ]);
                }
            }
            $this->merge(['sessions' => $cleanedSessions]);
        }
    }

    /**
     * Get the validation attributes that apply to the request.
     */
    public function attributes(): array
    {
        return [
            'starts_at' => 'start date and time',
            'ends_at' => 'end date and time',
            'max_attendees' => 'maximum attendees',
            'speaker_requirements' => 'speaker requirements',
            'call_for_speakers_id' => 'call for speakers',
            'recurrence_settings.frequency' => 'recurrence frequency',
            'recurrence_settings.interval' => 'recurrence interval',
            'recurrence_settings.days_of_week' => 'recurrence days',
            'recurrence_settings.end_date' => 'recurrence end date',
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $event = $this->route('event');

        if ($event->status === 'completed' && $this->status !== 'completed') {
            $this->validator->errors()->add('status', 'Cannot change status of a completed event.');
        }
    }
}
