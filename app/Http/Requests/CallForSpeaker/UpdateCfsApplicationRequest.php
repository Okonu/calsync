<?php

namespace App\Http\Requests\CallForSpeaker;

use App\Models\CfsApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCfsApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('application');
        $cfs = $this->route('cfs');

        if (!$application || !$cfs) {
            return false;
        }

        $community = $cfs->community;
        return auth()->check() && auth()->user()->canManageCommunity($community);
    }

    public function rules(): array
    {
        $application = $this->route('application');
        $action = $this->route()->getActionMethod();

        $rules = [];

        switch ($action) {
            case 'approve':
                $rules = [
                    'notes' => 'nullable|string|max:1000',
                    'auto_assign_speaker' => 'boolean',
                    'speaker_assignment' => 'nullable|array',
                    'speaker_assignment.is_featured' => 'boolean',
                    'speaker_assignment.speaking_order' => 'nullable|integer|min:1|max:100',
                ];
                break;

            case 'reject':
                $rules = [
                    'reason' => 'required|string|max:1000',
                    'send_notification' => 'boolean',
                    'custom_email_message' => 'nullable|string|max:2000',
                ];
                break;

            case 'updateNotes':
                $rules = [
                    'notes' => 'required|string|max:1000',
                    'internal_notes' => 'nullable|string|max:1000',
                ];
                break;

            case 'updateStatus':
                $rules = [
                    'status' => [
                        'required',
                        Rule::in(['pending', 'approved', 'rejected', 'withdrawn'])
                    ],
                    'reason' => 'required_if:status,rejected|nullable|string|max:1000',
                    'notes' => 'nullable|string|max:1000',
                ];
                break;

            case 'bulkApprove':
                $rules = [
                    'application_ids' => 'required|array|min:1',
                    'application_ids.*' => [
                        'required',
                        'integer',
                        Rule::exists('cfs_applications', 'id')->where(function ($query) {
                            $cfs = $this->route('cfs');
                            $query->where('call_for_speakers_id', $cfs->id)
                                ->where('status', 'pending');
                        })
                    ],
                    'notes' => 'nullable|string|max:1000',
                    'auto_assign_speakers' => 'boolean',
                ];
                break;

            case 'bulkReject':
                $rules = [
                    'application_ids' => 'required|array|min:1',
                    'application_ids.*' => [
                        'required',
                        'integer',
                        Rule::exists('cfs_applications', 'id')->where(function ($query) {
                            $cfs = $this->route('cfs');
                            $query->where('call_for_speakers_id', $cfs->id)
                                ->where('status', 'pending');
                        })
                    ],
                    'reason' => 'required|string|max:1000',
                    'send_notifications' => 'boolean',
                ];
                break;

            case 'updateSessionAssignment':
                $rules = [
                    'event_session_id' => [
                        'nullable',
                        Rule::exists('event_sessions', 'id')->where(function ($query) use ($application) {
                            if ($application && $application->communityEvent) {
                                $query->where('community_event_id', $application->communityEvent->id);
                            }
                        })
                    ],
                    'notes' => 'nullable|string|max:500',
                ];
                break;

            default:
                $rules = [
                    'admin_notes' => 'nullable|string|max:1000',
                    'internal_notes' => 'nullable|string|max:1000',
                    'priority' => 'nullable|integer|min:1|max:5',
                    'tags' => 'nullable|array',
                    'tags.*' => 'string|max:50',
                ];
                break;
        }

        return $rules;
    }

    public function messages(): array
    {
        $action = $this->route()->getActionMethod();

        $messages = [
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'reason.required' => 'A rejection reason is required.',
            'reason.max' => 'Rejection reason cannot exceed 1000 characters.',
            'status.required' => 'Status is required.',
            'status.in' => 'Please select a valid status.',
            'application_ids.required' => 'At least one application must be selected.',
            'application_ids.min' => 'At least one application must be selected.',
            'application_ids.*.exists' => 'One or more selected applications are invalid or not in pending status.',
        ];

        switch ($action) {
            case 'approve':
                $messages['notes.max'] = 'Approval notes cannot exceed 1000 characters.';
                $messages['speaker_assignment.speaking_order.min'] = 'Speaking order must be at least 1.';
                $messages['speaker_assignment.speaking_order.max'] = 'Speaking order cannot exceed 100.';
                break;

            case 'reject':
                $messages['reason.required'] = 'A rejection reason is required.';
                $messages['custom_email_message.max'] = 'Custom email message cannot exceed 2000 characters.';
                break;

            case 'updateNotes':
                $messages['notes.required'] = 'Notes are required.';
                break;

            case 'bulkApprove':
                $messages['notes.max'] = 'Bulk approval notes cannot exceed 1000 characters.';
                break;

            case 'bulkReject':
                $messages['reason.required'] = 'A rejection reason is required for all applications.';
                break;

            case 'updateSessionAssignment':
                $messages['event_session_id.exists'] = 'Selected session does not exist or is not part of this event.';
                break;
        }

        return $messages;
    }

    protected function prepareForValidation(): void
    {
        $action = $this->route()->getActionMethod();

        $this->merge([
            'send_notification' => $this->boolean('send_notification', true),
            'send_notifications' => $this->boolean('send_notifications', true),
            'auto_assign_speaker' => $this->boolean('auto_assign_speaker', false),
            'auto_assign_speakers' => $this->boolean('auto_assign_speakers', false),
        ]);

        if ($this->has('speaker_assignment') && is_array($this->speaker_assignment)) {
            $assignment = $this->speaker_assignment;
            $assignment['is_featured'] = $assignment['is_featured'] ?? false;
            $this->merge(['speaker_assignment' => $assignment]);
        }

        if ($this->has('tags') && is_array($this->tags)) {
            $tags = array_filter(array_unique($this->tags));
            $this->merge(['tags' => array_values($tags)]);
        }

        if (in_array($action, ['bulkApprove', 'bulkReject']) && $this->has('application_ids')) {
            $applicationIds = array_filter(array_unique($this->application_ids));
            $this->merge(['application_ids' => array_values($applicationIds)]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $application = $this->route('application');
            $action = $this->route()->getActionMethod();

            if (!$application) {
                return;
            }

            if (in_array($action, ['approve', 'reject']) && $application->status !== 'pending') {
                $validator->errors()->add('status',
                    'Only pending applications can be approved or rejected.'
                );
            }

            if ($action === 'approve' && $this->auto_assign_speaker && $application->eventSession) {
                $session = $application->eventSession;
                $currentSpeakers = $session->speakers()->confirmed()->count();

                if ($currentSpeakers >= $session->max_speakers) {
                    $validator->errors()->add('auto_assign_speaker',
                        'Cannot assign speaker - session has reached maximum speaker capacity.'
                    );
                }
            }

            if (in_array($action, ['bulkApprove', 'bulkReject'])) {
                $cfs = $this->route('cfs');
                $applicationIds = $this->application_ids ?? [];

                if (empty($applicationIds)) {
                    $validator->errors()->add('application_ids',
                        'No applications selected for bulk operation.'
                    );
                    return;
                }

                $validApplications = CfsApplication::where('call_for_speakers_id', $cfs->id)
                    ->whereIn('id', $applicationIds)
                    ->where('status', 'pending')
                    ->count();

                if ($validApplications !== count($applicationIds)) {
                    $validator->errors()->add('application_ids',
                        'Some selected applications are not valid for this operation.'
                    );
                }

                if ($action === 'bulkApprove' && $this->auto_assign_speakers) {
                    $applications = CfsApplication::whereIn('id', $applicationIds)
                        ->with('eventSession')
                        ->get();

                    foreach ($applications as $app) {
                        if ($app->eventSession) {
                            $currentSpeakers = $app->eventSession->speakers()->confirmed()->count();
                            if ($currentSpeakers >= $app->eventSession->max_speakers) {
                                $validator->errors()->add('auto_assign_speakers',
                                    "Cannot auto-assign speakers - session '{$app->eventSession->title}' has reached capacity."
                                );
                                break;
                            }
                        }
                    }
                }
            }

            if ($action === 'updateSessionAssignment' && $this->event_session_id) {
                $session = \App\Models\EventSession::find($this->event_session_id);
                if ($session && $application->communityEvent) {
                    if ($session->community_event_id !== $application->communityEvent->id) {
                        $validator->errors()->add('event_session_id',
                            'Session must belong to the same event as the application.'
                        );
                    }

                    $currentSpeakers = $session->speakers()->confirmed()->count();
                    if ($currentSpeakers >= $session->max_speakers) {
                        $validator->errors()->add('event_session_id',
                            'Selected session has reached maximum speaker capacity.'
                        );
                    }
                }
            }

            if ($this->has('priority') && !is_null($this->priority)) {
                if (!in_array($this->priority, [1, 2, 3, 4, 5])) {
                    $validator->errors()->add('priority',
                        'Priority must be between 1 (lowest) and 5 (highest).'
                    );
                }
            }
        });
    }

    /**
     * Get the validation attributes that apply to the request.
     */
    public function attributes(): array
    {
        return [
            'application_ids' => 'selected applications',
            'event_session_id' => 'session assignment',
            'speaker_assignment.speaking_order' => 'speaking order',
            'speaker_assignment.is_featured' => 'featured speaker status',
            'custom_email_message' => 'custom email message',
            'internal_notes' => 'internal notes',
            'admin_notes' => 'admin notes',
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $action = $this->route()->getActionMethod();
        $application = $this->route('application');

        if (in_array($action, ['approve', 'reject', 'bulkApprove', 'bulkReject'])) {
            \Log::info('CFS Application action performed', [
                'action' => $action,
                'application_id' => $application?->id,
                'cfs_id' => $this->route('cfs')?->id,
                'user_id' => auth()->id(),
                'application_ids' => $this->application_ids ?? null,
            ]);
        }
    }
}
