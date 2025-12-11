<?php

namespace App\Http\Requests\EventSession;

use App\Models\CommunityEvent;
use Illuminate\Foundation\Http\FormRequest;

class CreateEventSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $community = $this->route('community');
        $event = $this->route('event');

        return auth()->check() &&
            auth()->user()->canManageCommunity($community) &&
            $event->community_id === $community->id;
    }

    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'starts_at' => [
                'required',
                'date',
                'after:' . ($event->starts_at ?? 'now'),
                'before:' . ($event->ends_at ?? '+1 year')
            ],
            'ends_at' => 'required|date|after:starts_at',
            'max_speakers' => 'required|integer|min:1|max:10',
            'allows_applications' => 'boolean',
            'block_on_application' => 'boolean',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url|max:500',
            'requirements' => 'nullable|string|max:1000',
            'custom_fields' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Session title is required.',
            'title.max' => 'Session title cannot exceed 200 characters.',
            'description.max' => 'Session description cannot exceed 1000 characters.',
            'starts_at.required' => 'Session start time is required.',
            'starts_at.date' => 'Please provide a valid start date and time.',
            'starts_at.after' => 'Session must start after the event begins.',
            'starts_at.before' => 'Session must start before the event ends.',
            'ends_at.required' => 'Session end time is required.',
            'ends_at.date' => 'Please provide a valid end date and time.',
            'ends_at.after' => 'Session end time must be after start time.',
            'max_speakers.required' => 'Maximum number of speakers is required.',
            'max_speakers.min' => 'Each session must allow at least 1 speaker.',
            'max_speakers.max' => 'Each session can have a maximum of 10 speakers.',
            'location.max' => 'Location cannot exceed 255 characters.',
            'meeting_link.url' => 'Meeting link must be a valid URL.',
            'meeting_link.max' => 'Meeting link cannot exceed 500 characters.',
            'requirements.max' => 'Requirements cannot exceed 1000 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allows_applications' => $this->boolean('allows_applications', true),
            'block_on_application' => $this->boolean('block_on_application', true),
        ]);

        if ($this->has('custom_fields') && is_array($this->custom_fields)) {
            $cleanedFields = array_filter($this->custom_fields, function ($value) {
                return !is_null($value) && $value !== '';
            });
            $this->merge(['custom_fields' => empty($cleanedFields) ? null : $cleanedFields]);
        }

        $event = $this->route('event');
        if ($event && $event->is_online && !$this->meeting_link && !$this->location) {
            $this->merge(['location' => 'Online']);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $event = $this->route('event');

            if (!$event) {
                return;
            }

            if ($this->starts_at && $this->ends_at) {
                $sessionStart = \Carbon\Carbon::parse($this->starts_at);
                $sessionEnd = \Carbon\Carbon::parse($this->ends_at);

                if ($event->starts_at && $sessionStart->lt($event->starts_at)) {
                    $validator->errors()->add('starts_at',
                        'Session cannot start before the event begins.'
                    );
                }

                if ($event->ends_at && $sessionEnd->gt($event->ends_at)) {
                    $validator->errors()->add('ends_at',
                        'Session cannot end after the event ends.'
                    );
                }

                $overlappingSessions = $event->sessions()
                    ->where(function ($query) use ($sessionStart, $sessionEnd) {
                        $query->whereBetween('starts_at', [$sessionStart, $sessionEnd])
                            ->orWhereBetween('ends_at', [$sessionStart, $sessionEnd])
                            ->orWhere(function ($subQuery) use ($sessionStart, $sessionEnd) {
                                $subQuery->where('starts_at', '<=', $sessionStart)
                                    ->where('ends_at', '>=', $sessionEnd);
                            });
                    })
                    ->exists();

                if ($overlappingSessions) {
                    $validator->errors()->add('starts_at',
                        'This session overlaps with an existing session.'
                    );
                }
            }

            if ($event->is_online && !$this->meeting_link && !$this->location) {
                $validator->errors()->add('meeting_link',
                    'Online sessions require either a meeting link or location.'
                );
            }

            if ($event->max_attendees && $this->max_speakers > $event->max_attendees) {
                $validator->errors()->add('max_speakers',
                    'Session speaker limit cannot exceed event capacity.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'starts_at' => 'start time',
            'ends_at' => 'end time',
            'max_speakers' => 'maximum speakers',
            'allows_applications' => 'allow applications',
            'block_on_application' => 'block on pending applications',
            'meeting_link' => 'meeting link',
            'custom_fields' => 'custom fields',
        ];
    }
}
