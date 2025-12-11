<?php

namespace App\Http\Requests\EventSession;

use App\Models\EventSpeaker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventSessionSpeakerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $community = $this->route('community');
        return auth()->check() && auth()->user()->canManageCommunity($community);
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        switch ($action) {
            case 'addSpeaker':
                return $this->getAddSpeakerRules();
            case 'updateSpeaker':
                return $this->getUpdateSpeakerRules();
            case 'updateSpeakerOrder':
                return $this->getUpdateOrderRules();
            case 'bulkUpdateSpeakers':
                return $this->getBulkUpdateRules();
            default:
                return [];
        }
    }

    private function getAddSpeakerRules(): array
    {
        $session = $this->route('session');
        $event = $this->route('event');

        return [
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('event_speakers')
                    ->where('event_session_id', $session->id)
                    ->where('email', $this->email)
            ],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'company' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'topic_title' => 'nullable|string|max:200',
            'topic_description' => 'nullable|string|max:2000',
            'social_links' => 'nullable|array',
            'social_links.twitter' => 'nullable|url|max:255',
            'social_links.linkedin' => 'nullable|url|max:255',
            'social_links.github' => 'nullable|url|max:255',
            'social_links.website' => 'nullable|url|max:255',
            'social_links.instagram' => 'nullable|url|max:255',
            'assignment_type' => 'required|in:manual,cfs_application,invited',
            'status' => 'required|in:pending,confirmed,declined',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'notes' => 'nullable|string|max:1000',
            'cfs_application_id' => [
                'nullable',
                Rule::exists('cfs_applications', 'id')->where(function ($query) use ($event) {
                    $query->where('community_event_id', $event->id)
                        ->where('status', 'approved');
                })
            ],
        ];
    }

    private function getUpdateSpeakerRules(): array
    {
        $speaker = $this->route('speaker');
        $session = $this->route('session');

        return [
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('event_speakers')
                    ->ignore($speaker->id)
                    ->where('event_session_id', $session->id)
            ],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'company' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'topic_title' => 'nullable|string|max:200',
            'topic_description' => 'nullable|string|max:2000',
            'social_links' => 'nullable|array',
            'social_links.twitter' => 'nullable|url|max:255',
            'social_links.linkedin' => 'nullable|url|max:255',
            'social_links.github' => 'nullable|url|max:255',
            'social_links.website' => 'nullable|url|max:255',
            'social_links.instagram' => 'nullable|url|max:255',
            'status' => [
                'required',
                Rule::in($this->getAllowedStatuses($speaker))
            ],
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    private function getUpdateOrderRules(): array
    {
        $session = $this->route('session');

        return [
            'speakers' => 'required|array|min:1',
            'speakers.*.id' => [
                'required',
                'integer',
                Rule::exists('event_speakers', 'id')
                    ->where('event_session_id', $session->id)
            ],
            'speakers.*.sort_order' => 'required|integer|min:0',
        ];
    }

    private function getBulkUpdateRules(): array
    {
        $session = $this->route('session');

        return [
            'speaker_ids' => [
                'required',
                'array',
                'min:1',
                'max:50'
            ],
            'speaker_ids.*' => [
                'required',
                'integer',
                Rule::exists('event_speakers', 'id')
                    ->where('event_session_id', $session->id)
            ],
            'action' => [
                'required',
                Rule::in(['set_status', 'set_featured', 'unset_featured', 'remove_speakers', 'send_invitations'])
            ],
            'status' => [
                'required_if:action,set_status',
                'nullable',
                Rule::in(['pending', 'confirmed', 'declined'])
            ],
            'notes' => 'nullable|string|max:1000',
            'send_notifications' => 'boolean',
        ];
    }

    public function messages(): array
    {
        $action = $this->route()->getActionMethod();

        $messages = [
            'name.required' => 'Speaker name is required.',
            'name.max' => 'Speaker name cannot exceed 100 characters.',
            'email.required' => 'Speaker email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'A speaker with this email is already assigned to this session.',
            'email.max' => 'Email cannot exceed 100 characters.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'bio.max' => 'Bio cannot exceed 2000 characters.',
            'photo.image' => 'Photo must be an image file.',
            'photo.mimes' => 'Photo must be a JPEG, PNG, JPG, or GIF file.',
            'photo.max' => 'Photo file size must not exceed 2MB.',
            'company.max' => 'Company name cannot exceed 100 characters.',
            'job_title.max' => 'Job title cannot exceed 100 characters.',
            'topic_title.max' => 'Topic title cannot exceed 200 characters.',
            'topic_description.max' => 'Topic description cannot exceed 2000 characters.',
            'social_links.twitter.url' => 'Twitter URL must be a valid URL.',
            'social_links.linkedin.url' => 'LinkedIn URL must be a valid URL.',
            'social_links.github.url' => 'GitHub URL must be a valid URL.',
            'social_links.website.url' => 'Website URL must be a valid URL.',
            'social_links.instagram.url' => 'Instagram URL must be a valid URL.',
            'assignment_type.required' => 'Assignment type is required.',
            'assignment_type.in' => 'Please select a valid assignment type.',
            'status.required' => 'Speaker status is required.',
            'status.in' => 'Please select a valid speaker status.',
            'sort_order.min' => 'Sort order must be a positive number.',
            'sort_order.max' => 'Sort order cannot exceed 999.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'cfs_application_id.exists' => 'Selected CFS application does not exist or is not approved.',
        ];

        if ($action === 'updateSpeakerOrder') {
            $messages = array_merge($messages, [
                'speakers.required' => 'Speaker list is required.',
                'speakers.array' => 'Invalid speaker data format.',
                'speakers.min' => 'At least one speaker is required.',
                'speakers.*.id.required' => 'Speaker ID is required.',
                'speakers.*.id.exists' => 'Invalid speaker selected.',
                'speakers.*.sort_order.required' => 'Sort order is required for each speaker.',
                'speakers.*.sort_order.min' => 'Sort order must be a positive number.',
            ]);
        }

        if ($action === 'bulkUpdateSpeakers') {
            $messages = array_merge($messages, [
                'speaker_ids.required' => 'Please select at least one speaker.',
                'speaker_ids.min' => 'Please select at least one speaker.',
                'speaker_ids.max' => 'You can select a maximum of 50 speakers for bulk operations.',
                'speaker_ids.*.exists' => 'One or more selected speakers are invalid.',
                'action.required' => 'Please select an action to perform.',
                'action.in' => 'Please select a valid bulk action.',
                'status.required_if' => 'Status is required when setting speaker status.',
            ]);
        }

        return $messages;
    }

    protected function prepareForValidation(): void
    {
        $action = $this->route()->getActionMethod();

        if (in_array($action, ['addSpeaker', 'updateSpeaker'])) {
            $this->prepareSpeakerData();
        }

        if ($action === 'updateSpeakerOrder') {
            $this->prepareSpeakerOrderData();
        }

        if ($action === 'bulkUpdateSpeakers') {
            $this->prepareBulkData();
        }
    }

    private function prepareSpeakerData(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured', false),
        ]);

        if ($this->route()->getActionMethod() === 'addSpeaker' && !$this->has('assignment_type')) {
            $this->merge(['assignment_type' => 'manual']);
        }

        if ($this->route()->getActionMethod() === 'addSpeaker' && !$this->has('status')) {
            $this->merge(['status' => 'confirmed']);
        }

        if ($this->route()->getActionMethod() === 'addSpeaker' && !$this->has('sort_order')) {
            $session = $this->route('session');
            $maxOrder = $session->speakers()->max('sort_order') ?? -1;
            $this->merge(['sort_order' => $maxOrder + 1]);
        }

        if ($this->has('social_links') && is_array($this->social_links)) {
            $cleanedLinks = array_filter($this->social_links, function ($value) {
                return !empty($value);
            });
            $this->merge(['social_links' => empty($cleanedLinks) ? null : $cleanedLinks]);
        }

        $textFields = ['name', 'bio', 'company', 'job_title', 'topic_title', 'topic_description', 'notes'];
        foreach ($textFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => trim($this->$field) ?: null]);
            }
        }
    }

    private function prepareSpeakerOrderData(): void
    {
        if ($this->has('speakers') && is_array($this->speakers)) {
            $cleanedSpeakers = array_map(function ($speaker) {
                return [
                    'id' => (int) ($speaker['id'] ?? 0),
                    'sort_order' => (int) ($speaker['sort_order'] ?? 0),
                ];
            }, $this->speakers);

            $this->merge(['speakers' => $cleanedSpeakers]);
        }
    }

    private function prepareBulkData(): void
    {
        if ($this->has('speaker_ids') && is_array($this->speaker_ids)) {
            $this->merge([
                'speaker_ids' => array_map('intval', array_filter($this->speaker_ids))
            ]);
        }

        $this->merge([
            'send_notifications' => $this->boolean('send_notifications', true),
        ]);

        if ($this->has('notes')) {
            $this->merge([
                'notes' => trim($this->notes) ?: null
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $action = $this->route()->getActionMethod();

            switch ($action) {
                case 'addSpeaker':
                    $this->validateAddSpeaker($validator);
                    break;
                case 'updateSpeaker':
                    $this->validateUpdateSpeaker($validator);
                    break;
                case 'updateSpeakerOrder':
                    $this->validateSpeakerOrder($validator);
                    break;
                case 'bulkUpdateSpeakers':
                    $this->validateBulkUpdate($validator);
                    break;
            }
        });
    }

    private function validateAddSpeaker($validator): void
    {
        $session = $this->route('session');

        if ($session->is_full) {
            $validator->errors()->add('general',
                'Cannot add speaker - session is already full.'
            );
        }

        if ($session->status === 'cancelled') {
            $validator->errors()->add('general',
                'Cannot add speakers to cancelled sessions.'
            );
        }

        $event = $session->communityEvent;
        $existingSpeaker = $event->speakers()
            ->where('email', $this->email)
            ->where('event_session_id', '!=', $session->id)
            ->first();

        if ($existingSpeaker) {
            $validator->errors()->add('email',
                'This speaker is already assigned to another session in this event.'
            );
        }

        if ($session->requirements && (!$this->topic_title || !$this->topic_description)) {
            $validator->errors()->add('topic_title',
                'Topic title and description are required for this session.'
            );
        }

        if ($this->cfs_application_id) {
            $this->validateCfsApplication($validator);
        }
    }

    private function validateUpdateSpeaker($validator): void
    {
        $speaker = $this->route('speaker');
        $session = $this->route('session');

        $this->validateStatusTransition($validator, $speaker);

        if ($session->requirements && (!$this->topic_title || !$this->topic_description)) {
            $validator->errors()->add('topic_title',
                'Topic title and description are required for this session.'
            );
        }

        $this->validateEventConstraints($validator, $speaker);
    }

    private function validateSpeakerOrder($validator): void
    {
        $session = $this->route('session');

        if (!$this->speakers) {
            return;
        }

        $sessionSpeakerIds = $session->speakers()->pluck('id')->toArray();
        $providedSpeakerIds = array_column($this->speakers, 'id');

        $invalidSpeakers = array_diff($providedSpeakerIds, $sessionSpeakerIds);
        if (!empty($invalidSpeakers)) {
            $validator->errors()->add('speakers',
                'Some speakers do not belong to this session.'
            );
        }

        $sortOrders = array_column($this->speakers, 'sort_order');
        if (count($sortOrders) !== count(array_unique($sortOrders))) {
            $validator->errors()->add('speakers',
                'Duplicate sort orders are not allowed.'
            );
        }

        if (count($providedSpeakerIds) !== count($sessionSpeakerIds)) {
            $validator->errors()->add('speakers',
                'All session speakers must be included in the order update.'
            );
        }
    }

    private function validateBulkUpdate($validator): void
    {
        $session = $this->route('session');
        $speakers = EventSpeaker::whereIn('id', $this->speaker_ids ?? [])
            ->where('event_session_id', $session->id)
            ->get();

        if ($speakers->count() !== count($this->speaker_ids ?? [])) {
            $validator->errors()->add('speaker_ids',
                'Some selected speakers are invalid or do not belong to this session.'
            );
            return;
        }

        switch ($this->action) {
            case 'set_status':
                $this->validateBulkStatusChange($validator, $speakers);
                break;
            case 'remove_speakers':
                $this->validateBulkRemoval($validator, $speakers);
                break;
            case 'send_invitations':
                $this->validateBulkInvitations($validator, $speakers);
                break;
        }
    }

    private function validateBulkStatusChange($validator, $speakers): void
    {
        $newStatus = $this->status;
        $session = $speakers->first()->eventSession;
        $invalidTransitions = [];

        foreach ($speakers as $speaker) {
            if ($newStatus === 'confirmed' && $speaker->status !== 'confirmed') {
                if ($session->is_full) {
                    $invalidTransitions[] = "{$speaker->name} (session is full)";
                }
            }

            if ($session->starts_at->isPast() && $speaker->status === 'confirmed' && $newStatus !== 'confirmed') {
                $invalidTransitions[] = "{$speaker->name} (session has started)";
            }
        }

        if (!empty($invalidTransitions)) {
            $validator->errors()->add('status',
                'Invalid status transitions: ' . implode('; ', $invalidTransitions)
            );
        }
    }

    private function validateBulkRemoval($validator, $speakers): void
    {
        $session = $speakers->first()->eventSession;
        $invalidRemovals = [];

        foreach ($speakers as $speaker) {
            if ($session->starts_at->isPast() && $speaker->status === 'confirmed') {
                $invalidRemovals[] = "{$speaker->name} (session has started)";
            }

            if ($speaker->cfs_application_id) {
                $invalidRemovals[] = "{$speaker->name} (linked to CFS application)";
            }
        }

        if (!empty($invalidRemovals)) {
            $validator->errors()->add('action',
                'Cannot remove speakers: ' . implode('; ', $invalidRemovals)
            );
        }
    }

    private function validateBulkInvitations($validator, $speakers): void
    {
        $pendingSpeakers = $speakers->where('status', 'pending');

        if ($pendingSpeakers->isEmpty()) {
            $validator->errors()->add('action',
                'No pending speakers selected. Only pending speakers can receive invitations.'
            );
        }

        $invalidEmails = $pendingSpeakers->filter(function ($speaker) {
            return empty($speaker->email) || !filter_var($speaker->email, FILTER_VALIDATE_EMAIL);
        });

        if ($invalidEmails->isNotEmpty()) {
            $speakerNames = $invalidEmails->pluck('name')->join(', ');
            $validator->errors()->add('action',
                "Cannot send invitations to speakers with invalid emails: {$speakerNames}."
            );
        }
    }

    private function validateCfsApplication($validator): void
    {
        $application = \App\Models\CfsApplication::find($this->cfs_application_id);

        if ($application) {
            $session = $this->route('session');

            if ($application->event_session_id !== $session->id) {
                $validator->errors()->add('cfs_application_id',
                    'CFS application does not belong to this session.'
                );
            }

            if (EventSpeaker::where('cfs_application_id', $this->cfs_application_id)->exists()) {
                $validator->errors()->add('cfs_application_id',
                    'Speaker already exists for this application.'
                );
            }
        }
    }

    private function validateStatusTransition($validator, EventSpeaker $speaker): void
    {
        $currentStatus = $speaker->status;
        $newStatus = $this->status;
        $session = $speaker->eventSession;

        if ($session->starts_at->isPast() && $currentStatus === 'confirmed' && $newStatus !== 'confirmed') {
            $validator->errors()->add('status',
                'Cannot change status of confirmed speakers for sessions that have started.'
            );
        }

        if ($currentStatus !== 'confirmed' && $newStatus === 'confirmed' && $session->is_full) {
            $validator->errors()->add('status',
                'Cannot confirm speaker - session is already full.'
            );
        }
    }

    private function validateEventConstraints($validator, EventSpeaker $speaker): void
    {
        $event = $speaker->communityEvent;

        if ($event->starts_at && $event->starts_at->isPast() && $speaker->status === 'confirmed') {
            $allowedChanges = ['bio', 'photo', 'social_links', 'notes', 'topic_description'];
            $changedFields = array_keys($this->only([
                'name', 'email', 'phone', 'company', 'job_title', 'topic_title', 'status', 'is_featured', 'sort_order'
            ]));

            $restrictedChanges = array_diff($changedFields, $allowedChanges);

            if (!empty($restrictedChanges) && $this->hasChanges($speaker, $restrictedChanges)) {
                $validator->errors()->add('general',
                    'Only bio, photo, social links, notes, and topic description can be updated for confirmed speakers in started events.'
                );
            }
        }
    }

    /**
     * Check if speaker has changes in specified fields
     */
    private function hasChanges(EventSpeaker $speaker, array $fields): bool
    {
        foreach ($fields as $field) {
            if ($this->has($field) && $this->$field !== $speaker->$field) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get allowed statuses based on speaker state
     */
    private function getAllowedStatuses(EventSpeaker $speaker): array
    {
        $statuses = ['pending', 'confirmed', 'declined'];

        if ($speaker->eventSession->starts_at && $speaker->eventSession->starts_at->isPast()) {
            if ($speaker->status === 'confirmed') {
                $statuses = ['confirmed', 'declined'];
            }
        }

        return $statuses;
    }

    public function attributes(): array
    {
        return [
            'job_title' => 'job title',
            'topic_title' => 'topic title',
            'topic_description' => 'topic description',
            'social_links.twitter' => 'Twitter URL',
            'social_links.linkedin' => 'LinkedIn URL',
            'social_links.github' => 'GitHub URL',
            'social_links.website' => 'Website URL',
            'social_links.instagram' => 'Instagram URL',
            'assignment_type' => 'assignment type',
            'is_featured' => 'featured speaker',
            'sort_order' => 'speaker order',
            'cfs_application_id' => 'CFS application',
            'speaker_ids' => 'selected speakers',
            'send_notifications' => 'send notifications',
        ];
    }
}
