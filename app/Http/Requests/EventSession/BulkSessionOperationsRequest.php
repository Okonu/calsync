<?php

namespace App\Http\Requests\EventSession;

use App\Models\EventSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkSessionOperationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $community = $this->route('community');
        return auth()->check() && auth()->user()->canManageCommunity($community);
    }

    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'session_ids' => [
                'required',
                'array',
                'min:1',
                'max:50'
            ],
            'session_ids.*' => [
                'required',
                'integer',
                Rule::exists('event_sessions', 'id')
                    ->where('community_event_id', $event->id)
            ],
            'action' => [
                'required',
                Rule::in($this->getAllowedActions())
            ],
            'status' => [
                'required_if:action,set_status',
                'nullable',
                Rule::in(['available', 'pending', 'confirmed', 'full', 'cancelled'])
            ],
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'session_ids.required' => 'Please select at least one session.',
            'session_ids.array' => 'Invalid session selection format.',
            'session_ids.min' => 'Please select at least one session.',
            'session_ids.max' => 'You can select a maximum of 50 sessions for bulk operations.',
            'session_ids.*.required' => 'All session selections must be valid.',
            'session_ids.*.exists' => 'One or more selected sessions are invalid or do not belong to this event.',
            'action.required' => 'Please select an action to perform.',
            'action.in' => 'Please select a valid bulk action.',
            'status.required_if' => 'Status is required when setting session status.',
            'status.in' => 'Please select a valid session status.',
            'reason.max' => 'Reason cannot exceed 500 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('session_ids') && is_array($this->session_ids)) {
            $this->merge([
                'session_ids' => array_map('intval', array_filter($this->session_ids))
            ]);
        }

        if ($this->has('reason')) {
            $this->merge([
                'reason' => trim($this->reason) ?: null
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateBulkOperation($validator);
        });
    }

    private function validateBulkOperation($validator): void
    {
        if (empty($this->session_ids)) {
            return;
        }

        $event = $this->route('event');
        $sessions = EventSession::whereIn('id', $this->session_ids)
            ->where('community_event_id', $event->id)
            ->get();

        if ($sessions->count() !== count($this->session_ids)) {
            $validator->errors()->add('session_ids',
                'Some selected sessions are invalid or do not belong to this event.'
            );
            return;
        }

        switch ($this->action) {
            case 'enable_applications':
                $this->validateEnableApplications($validator, $sessions);
                break;
            case 'disable_applications':
                $this->validateDisableApplications($validator, $sessions);
                break;
            case 'enable_blocking':
            case 'disable_blocking':
                $this->validateBlockingChanges($validator, $sessions);
                break;
            case 'set_status':
                $this->validateStatusChange($validator, $sessions);
                break;
        }
    }

    private function validateEnableApplications($validator, $sessions): void
    {
        $invalidSessions = $sessions->filter(function ($session) {
            return $session->status === 'cancelled' ||
                $session->is_full ||
                $session->starts_at->isPast();
        });

        if ($invalidSessions->isNotEmpty()) {
            $sessionTitles = $invalidSessions->pluck('title')->join(', ');
            $validator->errors()->add('action',
                "Cannot enable applications for sessions: {$sessionTitles} (cancelled, full, or in the past)."
            );
        }
    }

    private function validateDisableApplications($validator, $sessions): void
    {
        $sessionsWithPendingApps = $sessions->filter(function ($session) {
            return $session->has_pending_applications;
        });

        if ($sessionsWithPendingApps->isNotEmpty()) {
            $sessionTitles = $sessionsWithPendingApps->pluck('title')->join(', ');
            $validator->errors()->add('action',
                "Cannot disable applications for sessions with pending applications: {$sessionTitles}. Please review applications first."
            );
        }
    }

    private function validateBlockingChanges($validator, $sessions): void
    {
        if ($this->action === 'enable_blocking') {
            $sessionsWithPendingApps = $sessions->filter(function ($session) {
                return $session->has_pending_applications && !$session->block_on_application;
            });

            if ($sessionsWithPendingApps->isNotEmpty()) {
                $sessionTitles = $sessionsWithPendingApps->pluck('title')->join(', ');
                $validator->errors()->add('action',
                    "Enabling blocking for sessions with pending applications will change their status: {$sessionTitles}."
                );
            }
        }
    }

    private function validateStatusChange($validator, $sessions): void
    {
        if (!$this->status) {
            return;
        }

        $invalidTransitions = [];

        foreach ($sessions as $session) {
            $currentStatus = $session->status;
            $newStatus = $this->status;

            if ($newStatus === 'available' && $session->current_speakers > 0) {
                $invalidTransitions[] = "{$session->title} (has speakers, cannot set to available)";
            }

            if ($newStatus === 'available' && $session->is_full) {
                $invalidTransitions[] = "{$session->title} (is full, cannot set to available)";
            }

            if ($currentStatus === 'cancelled' && !in_array($newStatus, ['available', 'cancelled'])) {
                $invalidTransitions[] = "{$session->title} (cancelled sessions can only be set to available)";
            }

            if ($newStatus === 'confirmed' && $session->current_speakers === 0) {
                $invalidTransitions[] = "{$session->title} (no speakers, cannot confirm)";
            }

            if ($newStatus === 'full' && !$session->is_full) {
                $invalidTransitions[] = "{$session->title} (not at capacity, cannot set to full)";
            }
        }

        if (!empty($invalidTransitions)) {
            $validator->errors()->add('status',
                'Invalid status transitions: ' . implode('; ', $invalidTransitions)
            );
        }
    }

    /**
     * Get allowed bulk actions
     */
    private function getAllowedActions(): array
    {
        return [
            'enable_applications',
            'disable_applications',
            'enable_blocking',
            'disable_blocking',
            'set_status',
        ];
    }

    /**
     * Get validation attributes
     */
    public function attributes(): array
    {
        return [
            'session_ids' => 'selected sessions',
            'action' => 'bulk action',
            'status' => 'session status',
        ];
    }

    /**
     * Handle a passed validation attempt
     */
    protected function passedValidation(): void
    {
        \Log::info('Bulk session operation performed', [
            'action' => $this->action,
            'session_count' => count($this->session_ids),
            'event_id' => $this->route('event')->id,
            'community_id' => $this->route('community')->id,
            'user_id' => auth()->id(),
            'status' => $this->status,
            'reason' => $this->reason,
        ]);
    }
}
