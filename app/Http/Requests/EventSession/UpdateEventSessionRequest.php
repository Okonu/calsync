<?php

namespace App\Http\Requests\EventSession;

use App\Models\EventSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $community = $this->route('community');
        $session = $this->route('session');

        return auth()->check() &&
            auth()->user()->canManageCommunity($community) &&
            $session->communityEvent->community_id === $community->id;
    }

    public function rules(): array
    {
        $event = $this->route('event');
        $session = $this->route('session');

        return [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'starts_at' => [
                'required',
                'date',
                $this->getDateValidationRule($event, 'starts_at')
            ],
            'ends_at' => 'required|date|after:starts_at',
            'max_speakers' => [
                'required',
                'integer',
                'min:' . ($session->current_speakers ?? 1),
                'max:10'
            ],
            'allows_applications' => 'boolean',
            'block_on_application' => 'boolean',
            'status' => [
                'required',
                Rule::in($this->getAllowedStatuses($session))
            ],
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url|max:500',
            'requirements' => 'nullable|string|max:1000',
            'custom_fields' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        $session = $this->route('session');
        $currentSpeakers = $session->current_speakers ?? 0;

        return [
            'title.required' => 'Session title is required.',
            'title.max' => 'Session title cannot exceed 200 characters.',
            'description.max' => 'Session description cannot exceed 1000 characters.',
            'starts_at.required' => 'Session start time is required.',
            'starts_at.date' => 'Please provide a valid start date and time.',
            'ends_at.required' => 'Session end time is required.',
            'ends_at.date' => 'Please provide a valid end date and time.',
            'ends_at.after' => 'Session end time must be after start time.',
            'max_speakers.required' => 'Maximum number of speakers is required.',
            'max_speakers.min' => "Maximum speakers cannot be less than current speakers ({$currentSpeakers}).",
            'max_speakers.max' => 'Each session can have a maximum of 10 speakers.',
            'status.required' => 'Session status is required.',
            'status.in' => 'Please select a valid session status.',
            'location.max' => 'Location cannot exceed 255 characters.',
            'meeting_link.url' => 'Meeting link must be a valid URL.',
            'meeting_link.max' => 'Meeting link cannot exceed 500 characters.',
            'requirements.max' => 'Requirements cannot exceed 1000 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allows_applications' => $this->boolean('allows_applications'),
            'block_on_application' => $this->boolean('block_on_application'),
        ]);

        if ($this->has('custom_fields') && is_array($this->custom_fields)) {
            $cleanedFields = array_filter($this->custom_fields, function ($value) {
                return !is_null($value) && $value !== '';
            });
            $this->merge(['custom_fields' => empty($cleanedFields) ? null : $cleanedFields]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $event = $this->route('event');
            $session = $this->route('session');

            if (!$event || !$session) {
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
                    ->where('id', '!=', $session->id)
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

            $this->validateStatusTransition($validator, $session);

            if (!$this->allows_applications && $session->has_pending_applications) {
                $validator->errors()->add('allows_applications',
                    'Cannot disable applications while pending applications exist.'
                );
            }

            if ($this->max_speakers < $session->current_speakers) {
                $validator->errors()->add('max_speakers',
                    "Cannot reduce max speakers below current speaker count ({$session->current_speakers})."
                );
            }

            if ($event->is_online && !$this->meeting_link && !$this->location) {
                $validator->errors()->add('meeting_link',
                    'Online sessions require either a meeting link or location.'
                );
            }
        });
    }

    /**
     * Get date validation rule based on session status and timing
     */
    private function getDateValidationRule($event, $field): string
    {
        $session = $this->route('session');

        if ($session && ($session->current_speakers > 0 || $session->starts_at->isPast())) {
            return $field === 'starts_at' ? 'before:' . ($event->ends_at ?? '+1 year') : '';
        }

        return $field === 'starts_at'
            ? 'after:now|before:' . ($event->ends_at ?? '+1 year')
            : '';
    }

    /**
     * Get allowed statuses based on session state
     */
    private function getAllowedStatuses(EventSession $session): array
    {
        $statuses = ['available', 'pending', 'confirmed', 'full', 'cancelled'];

        if ($session->current_speakers > 0) {
            $statuses = array_diff($statuses, ['available']);
        }

        if ($session->is_full) {
            $statuses[] = 'full';
        }

        return array_unique($statuses);
    }

    /**
     * Validate status transitions
     */
    private function validateStatusTransition($validator, EventSession $session): void
    {
        $currentStatus = $session->status;
        $newStatus = $this->status;

        if ($currentStatus === 'cancelled' && !in_array($newStatus, ['available', 'cancelled'])) {
            $validator->errors()->add('status',
                'Cancelled sessions can only be set to available or remain cancelled.'
            );
        }

        if ($newStatus === 'available' && $session->is_full) {
            $validator->errors()->add('status',
                'Cannot set full session to available without reducing speakers first.'
            );
        }

        if ($newStatus === 'available' && $session->current_speakers > 0) {
            $validator->errors()->add('status',
                'Cannot set session with speakers to available status.'
            );
        }
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
