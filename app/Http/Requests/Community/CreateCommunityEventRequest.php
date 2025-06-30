<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommunityEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $community = $this->route('community');
        return auth()->check() && auth()->user()->canManageCommunity($community);
    }

    public function rules(): array
    {
        $community = $this->route('community');

        return [
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:50|alpha_dash|unique:community_events,slug,NULL,id,community_id,' . $community->id,
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:webinar,workshop,study_jam,meetup,conference,other',
            'starts_at' => 'nullable|date|after:now',
            'ends_at' => 'nullable|date|after:starts_at',
            'location' => 'nullable|string|max:255',

            // Meeting platform fields
            'meeting_platform' => 'nullable|in:google_meet,zoom,teams,webex,discord,custom',
            'meeting_link' => 'nullable|url|max:500',
            'auto_create_google_meet' => 'boolean',
            'create_calendar_event' => 'boolean',

            'is_online' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_settings' => 'nullable|array',
            'recurrence_settings.frequency' => 'required_if:is_recurring,true|in:daily,weekly,monthly',
            'recurrence_settings.interval' => 'required_if:is_recurring,true|integer|min:1|max:12',
            'recurrence_settings.days_of_week' => 'required_if:recurrence_settings.frequency,weekly|array',
            'recurrence_settings.end_date' => 'nullable|date|after:starts_at',
            'max_attendees' => 'nullable|integer|min:1|max:10000',
            'requires_approval' => 'boolean',
            'is_public' => 'boolean',
            'speaker_requirements' => 'nullable|string|max:1000',
            'call_for_speakers_id' => 'nullable|exists:calls_for_speakers,id',

            // Sessions
            'sessions' => 'nullable|array|max:20',
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
            'type.required' => 'Please select an event type.',
            'type.in' => 'Please select a valid event type.',
            'ends_at.after' => 'End time must be after start time.',
            'meeting_link.url' => 'Meeting link must be a valid URL.',
            'meeting_platform.in' => 'Please select a valid meeting platform.',
            'max_attendees.min' => 'Maximum attendees must be at least 1.',
            'max_attendees.max' => 'Maximum attendees cannot exceed 10,000.',
            'call_for_speakers_id.exists' => 'Selected call for speakers does not exist.',

            // Session validation messages
            'sessions.max' => 'You can create a maximum of 20 sessions per event.',
            'sessions.*.title.required' => 'Session title is required.',
            'sessions.*.starts_at.required' => 'Session start time is required.',
            'sessions.*.ends_at.required' => 'Session end time is required.',
            'sessions.*.ends_at.after' => 'Session end time must be after start time.',
            'sessions.*.max_speakers.required' => 'Maximum speakers per session is required.',
            'sessions.*.max_speakers.min' => 'Each session must allow at least 1 speaker.',
            'sessions.*.max_speakers.max' => 'Each session can have a maximum of 10 speakers.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $community = $this->route('community');

        // Auto-generate slug from title if not provided
        if (!$this->has('slug') && $this->has('title')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->title)
            ]);
        }

        // Set defaults
        $this->merge([
            'is_online' => $this->boolean('is_online', true),
            'is_recurring' => $this->boolean('is_recurring', false),
            'requires_approval' => $this->boolean('requires_approval', false),
            'is_public' => $this->boolean('is_public', true),
            'auto_create_google_meet' => $this->boolean('auto_create_google_meet', false),
            'create_calendar_event' => $this->boolean('create_calendar_event', false),
        ]);

        // Handle meeting platform logic
        if ($this->boolean('is_online')) {
            // For Google Meet, auto-enable calendar creation if community has calendar
            if ($this->meeting_platform === 'google_meet' && $community->calendar_email) {
                $this->merge([
                    'auto_create_google_meet' => true,
                    'create_calendar_event' => true,
                    'meeting_link' => null, // Will be generated automatically
                ]);
            }

            // For other platforms, require meeting link if not Google Meet
            if ($this->meeting_platform && $this->meeting_platform !== 'google_meet') {
                // Clear auto-create flags
                $this->merge([
                    'auto_create_google_meet' => false,
                ]);
            }
        } else {
            // Physical event - clear online-specific fields
            $this->merge([
                'meeting_platform' => null,
                'meeting_link' => null,
                'auto_create_google_meet' => false,
            ]);
        }

        // Validate CFS belongs to community
        if ($this->has('call_for_speakers_id') && $this->call_for_speakers_id) {
            $cfs = \App\Models\CallForSpeakers::find($this->call_for_speakers_id);
            if (!$cfs || $cfs->community_id !== $community->id) {
                $this->merge(['call_for_speakers_id' => null]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $community = $this->route('community');

            // If online event, require either meeting platform or meeting link
            if ($this->boolean('is_online')) {
                if (empty($this->meeting_platform)) {
                    $validator->errors()->add('meeting_platform', 'Please select a meeting platform for online events.');
                }

                // For non-Google Meet platforms, require meeting link
                if ($this->meeting_platform && $this->meeting_platform !== 'google_meet' && empty($this->meeting_link)) {
                    $validator->errors()->add('meeting_link', 'Meeting link is required for ' . ucfirst(str_replace('_', ' ', $this->meeting_platform)) . '.');
                }

                // For Google Meet, ensure community has calendar setup
                if ($this->meeting_platform === 'google_meet' && empty($community->calendar_email)) {
                    $validator->errors()->add('meeting_platform', 'Google Meet requires community calendar setup. Please configure your community calendar first.');
                }
            }

            // For calendar event creation, ensure required fields are present
            if ($this->boolean('create_calendar_event')) {
                if (empty($community->calendar_email)) {
                    $validator->errors()->add('create_calendar_event', 'Calendar event creation requires community calendar setup.');
                }

                if (empty($this->starts_at) || empty($this->ends_at)) {
                    $validator->errors()->add('create_calendar_event', 'Start and end times are required for calendar event creation.');
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'meeting_platform' => 'meeting platform',
            'meeting_link' => 'meeting link',
            'auto_create_google_meet' => 'Google Meet auto-creation',
            'create_calendar_event' => 'calendar event creation',
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
}
