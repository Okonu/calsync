<?php

namespace App\Http\Requests\CallForSpeaker;

use App\Models\Community;
use App\Models\CallForSpeakers;
use App\Models\CommunityEvent;
use App\Models\EventSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCfsApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cfs = $this->getCfs();
        return $cfs && $cfs->acceptsApplications();
    }

    public function rules(): array
    {
        $cfs = $this->getCfs();

        if (!$cfs) {
            return [];
        }

        $rules = [
            'applicant_name' => 'required|string|max:100',
            'applicant_email' => [
                'required',
                'email',
                'max:100',
                $this->getEmailUniqueRule($cfs)
            ],
            'applicant_phone' => 'nullable|string|max:20',
            'target_type' => ['required', Rule::in($this->getAllowedTargetTypes($cfs))],
            'community_event_id' => 'required_if:target_type,event|nullable|exists:community_events,id',
            'event_session_id' => 'required_if:target_type,session|nullable|exists:event_sessions,id',
            'preferred_sessions' => 'nullable|array',
            'preferred_sessions.*' => 'exists:event_sessions,id',
            'custom_responses' => 'nullable|array',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png,gif',
        ];

        $requiredFields = $cfs->required_fields ?? [];
        foreach ($requiredFields as $field) {
            switch ($field) {
                case 'bio':
                    $rules['bio'] = 'required|string|max:2000';
                    break;
                case 'topic_title':
                    $rules['topic_title'] = 'required|string|max:200';
                    break;
                case 'topic_description':
                    $rules['topic_description'] = 'required|string|max:2000';
                    break;
                case 'topic_outline':
                    $rules['topic_outline'] = 'required|string|max:3000';
                    break;
                case 'experience_level':
                    $rules['experience_level'] = 'required|in:beginner,intermediate,advanced,expert';
                    break;
                case 'previous_speaking_experience':
                    $rules['previous_speaking_experience'] = 'required|string|max:2000';
                    break;
                case 'phone':
                    $rules['applicant_phone'] = 'required|string|max:20';
                    break;
            }
        }

        $optionalFields = ['bio', 'topic_title', 'topic_description', 'topic_outline', 'experience_level', 'previous_speaking_experience'];
        foreach ($optionalFields as $field) {
            if (!in_array($field, $requiredFields)) {
                switch ($field) {
                    case 'bio':
                        $rules['bio'] = 'nullable|string|max:2000';
                        break;
                    case 'topic_title':
                        $rules['topic_title'] = 'nullable|string|max:200';
                        break;
                    case 'topic_description':
                        $rules['topic_description'] = 'nullable|string|max:2000';
                        break;
                    case 'topic_outline':
                        $rules['topic_outline'] = 'nullable|string|max:3000';
                        break;
                    case 'experience_level':
                        $rules['experience_level'] = 'nullable|in:beginner,intermediate,advanced,expert';
                        break;
                    case 'previous_speaking_experience':
                        $rules['previous_speaking_experience'] = 'nullable|string|max:2000';
                        break;
                }
            }
        }

        if ($cfs->custom_questions) {
            foreach ($cfs->custom_questions as $index => $question) {
                $fieldName = "custom_responses.{$index}";
                $baseRule = $question['required'] ? 'required' : 'nullable';

                switch ($question['type']) {
                    case 'text':
                        $rules[$fieldName] = $baseRule . '|string|max:500';
                        break;
                    case 'textarea':
                        $rules[$fieldName] = $baseRule . '|string|max:2000';
                        break;
                    case 'select':
                    case 'radio':
                        if (isset($question['options'])) {
                            $rules[$fieldName] = $baseRule . '|in:' . implode(',', $question['options']);
                        }
                        break;
                    case 'checkbox':
                        $rules[$fieldName] = $baseRule . '|array';
                        if (isset($question['options'])) {
                            $rules[$fieldName . '.*'] = 'in:' . implode(',', $question['options']);
                        }
                        break;
                }
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'applicant_name.required' => 'Your name is required.',
            'applicant_email.required' => 'Your email address is required.',
            'applicant_email.email' => 'Please provide a valid email address.',
            'applicant_email.unique' => 'You have already applied to this target.',
            'applicant_phone.required' => 'Phone number is required.',
            'bio.required' => 'Bio is required.',
            'topic_title.required' => 'Topic title is required.',
            'topic_description.required' => 'Topic description is required.',
            'topic_outline.required' => 'Topic outline is required.',
            'experience_level.required' => 'Please select your experience level.',
            'experience_level.in' => 'Please select a valid experience level.',
            'previous_speaking_experience.required' => 'Previous speaking experience is required.',
            'target_type.required' => 'Please select whether you\'re applying for an event or session.',
            'target_type.in' => 'Invalid application target type for this call for speakers.',
            'community_event_id.required_if' => 'Please select an event.',
            'event_session_id.required_if' => 'Please select a session.',
            'attachments.max' => 'You can upload a maximum of 5 attachments.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.max' => 'Each attachment must not exceed 10MB.',
            'attachments.*.mimes' => 'Attachments must be PDF, Word documents, text files, or images.',
        ];

        $cfs = $this->getCfs();
        if ($cfs && $cfs->custom_questions) {
            foreach ($cfs->custom_questions as $index => $question) {
                $fieldName = "custom_responses.{$index}";
                $messages[$fieldName . '.required'] = "'{$question['question']}' is required.";

                if ($question['type'] === 'checkbox') {
                    $messages[$fieldName . '.array'] = "Please select valid options for '{$question['question']}'.";
                }
            }
        }

        return $messages;
    }

    protected function prepareForValidation(): void
    {
        $cfs = $this->getCfs();

        if (!$cfs) {
            return;
        }

        if ($this->has('custom_responses') && is_array($this->custom_responses)) {
            $cleanedResponses = [];
            foreach ($this->custom_responses as $key => $value) {
                if (!is_null($value) && $value !== '') {
                    $cleanedResponses[$key] = $value;
                }
            }
            $this->merge(['custom_responses' => $cleanedResponses]);
        }

        if ($this->has('preferred_sessions') && is_array($this->preferred_sessions)) {
            $this->merge([
                'preferred_sessions' => array_filter($this->preferred_sessions)
            ]);
        }

        if ($this->target_type === 'event' && $this->community_event_id) {
            $community = $this->getCommunity();
            if ($community) {
                $event = CommunityEvent::where('id', $this->community_event_id)
                    ->where('community_id', $community->id)
                    ->first();

                if (!$event) {
                    $this->merge(['community_event_id' => null]);
                }
            }
        }

        if ($this->target_type === 'session' && $this->event_session_id) {
            $community = $this->getCommunity();
            if ($community) {
                $session = EventSession::where('id', $this->event_session_id)
                    ->whereHas('communityEvent', function ($query) use ($community) {
                        $query->where('community_id', $community->id);
                    })
                    ->first();

                if (!$session || !$session->canAcceptApplications()) {
                    $this->merge(['event_session_id' => null]);
                }
            }
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cfs = $this->getCfs();

            if (!$cfs) {
                $validator->errors()->add('general', 'Call for speakers not found.');
                return;
            }

            if (!$cfs->acceptsApplications()) {
                $validator->errors()->add('general',
                    'This call for speakers is not currently accepting applications.'
                );
            }

            if ($cfs->requires_login && !auth()->check()) {
                $validator->errors()->add('general',
                    'You must be logged in to apply to this call for speakers.'
                );
            }

            $applicationType = $cfs->application_type;
            if ($this->target_type === 'event' && !in_array($applicationType, ['event', 'both'])) {
                $validator->errors()->add('target_type',
                    'This call for speakers does not accept event applications.'
                );
            }

            if ($this->target_type === 'session' && !in_array($applicationType, ['session', 'both'])) {
                $validator->errors()->add('target_type',
                    'This call for speakers does not accept session applications.'
                );
            }

            if ($this->target_type === 'event' && $this->community_event_id) {
                $linkedEvents = $cfs->events()->pluck('id');
                if (!$linkedEvents->contains($this->community_event_id)) {
                    $validator->errors()->add('community_event_id',
                        'Selected event is not linked to this call for speakers.'
                    );
                }
            }

            if ($this->target_type === 'session' && $this->event_session_id) {
                $session = EventSession::find($this->event_session_id);
                if ($session) {
                    $linkedEvents = $cfs->events()->pluck('id');
                    if (!$linkedEvents->contains($session->community_event_id)) {
                        $validator->errors()->add('event_session_id',
                            'Selected session is not linked to this call for speakers.'
                        );
                    }
                }
            }
        });
    }

    /**
     * Get email unique validation rule
     */
    private function getEmailUniqueRule(CallForSpeakers $cfs): Rule
    {
        $rule = Rule::unique('cfs_applications')->where('call_for_speakers_id', $cfs->id);

        if (!$cfs->allow_multiple_applications) {
            return $rule->where('applicant_email', $this->applicant_email);
        }

        if ($this->target_type === 'session' && $this->event_session_id) {
            $rule->where('event_session_id', $this->event_session_id);
        }

        if ($this->target_type === 'event' && $this->community_event_id) {
            $rule->where('community_event_id', $this->community_event_id);
        }

        return $rule;
    }

    /**
     * Get allowed target types based on CFS configuration
     */
    private function getAllowedTargetTypes(CallForSpeakers $cfs): array
    {
        $types = [];

        if (in_array($cfs->application_type, ['event', 'both'])) {
            $types[] = 'event';
        }

        if (in_array($cfs->application_type, ['session', 'both'])) {
            $types[] = 'session';
        }

        return $types;
    }

    /**
     * Get the CFS instance from route parameters
     */
    private function getCfs(): ?CallForSpeakers
    {
        $communitySlug = $this->route('communitySlug');
        $cfsSlug = $this->route('cfsSlug');

        if (!$communitySlug || !$cfsSlug) {
            return null;
        }

        return CallForSpeakers::whereHas('community', function ($query) use ($communitySlug) {
            $query->where('slug', $communitySlug)
                ->where('is_public', true)
                ->where('is_active', true);
        })
            ->where('slug', $cfsSlug)
            ->where('is_public', true)
            ->first();
    }

    /**
     * Get the Community instance from route parameters
     */
    private function getCommunity(): ?Community
    {
        $communitySlug = $this->route('communitySlug');

        if (!$communitySlug) {
            return null;
        }

        return Community::where('slug', $communitySlug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get the validation attributes that apply to the request.
     */
    public function attributes(): array
    {
        return [
            'applicant_name' => 'name',
            'applicant_email' => 'email address',
            'applicant_phone' => 'phone number',
            'target_type' => 'application target',
            'community_event_id' => 'event',
            'event_session_id' => 'session',
            'experience_level' => 'experience level',
            'previous_speaking_experience' => 'previous speaking experience',
        ];
    }
}
