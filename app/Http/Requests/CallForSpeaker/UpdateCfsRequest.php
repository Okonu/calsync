<?php

namespace App\Http\Requests\CallForSpeaker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCfsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $community = $this->route('community');
        return auth()->check() && auth()->user()->canManageCommunity($community);
    }

    public function rules(): array
    {
        $community = $this->route('community');
        $cfs = $this->route('cfs');

        return [
            'title' => 'required|string|max:200',
            'slug' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('calls_for_speakers', 'slug')
                    ->ignore($cfs->id)
                    ->where('community_id', $community->id)
            ],
            'description' => 'nullable|string|max:2000',
            'guidelines' => 'nullable|string|max:2000',
            'opens_at' => 'nullable|date',
            'closes_at' => 'nullable|date|after:opens_at',
            'is_public' => 'boolean',
            'requires_login' => 'boolean',
            'show_application_count' => 'boolean',
            'allow_multiple_applications' => 'boolean',
            'application_type' => 'required|in:event,session,both',
            'auto_approve' => 'boolean',
            'status' => 'required|in:draft,open,closed,archived',

            // Required fields configuration
            'required_fields' => 'nullable|array',
            'required_fields.*' => 'in:bio,topic_title,topic_description,topic_outline,experience_level,previous_speaking_experience,phone',

            // Custom questions
            'custom_questions' => 'nullable|array|max:10',
            'custom_questions.*.id' => 'nullable|integer',
            'custom_questions.*.question' => 'required|string|max:500',
            'custom_questions.*.type' => 'required|in:text,textarea,select,radio,checkbox',
            'custom_questions.*.required' => 'boolean',
            'custom_questions.*.options' => 'required_if:custom_questions.*.type,select,radio,checkbox|array',
            'custom_questions.*.options.*' => 'string|max:200',

            // Email templates
            'acceptance_email_template' => 'nullable|string|max:2000',
            'rejection_email_template' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Call for speakers title is required.',
            'slug.unique' => 'A call for speakers with this slug already exists in this community.',
            'slug.alpha_dash' => 'Slug may only contain letters, numbers, dashes, and underscores.',
            'opens_at.date' => 'Please provide a valid opening date.',
            'closes_at.after' => 'Closing date must be after opening date.',
            'application_type.required' => 'Please select an application type.',
            'application_type.in' => 'Please select a valid application type.',
            'status.required' => 'Status is required.',
            'status.in' => 'Please select a valid status.',

            // Required fields validation
            'required_fields.array' => 'Required fields must be an array.',
            'required_fields.*.in' => 'Invalid required field selected.',

            // Custom questions validation
            'custom_questions.max' => 'You can add a maximum of 10 custom questions.',
            'custom_questions.*.question.required' => 'Question text is required.',
            'custom_questions.*.question.max' => 'Question text cannot exceed 500 characters.',
            'custom_questions.*.type.required' => 'Question type is required.',
            'custom_questions.*.type.in' => 'Please select a valid question type.',
            'custom_questions.*.options.required_if' => 'Options are required for select, radio, and checkbox questions.',
            'custom_questions.*.options.*.max' => 'Option text cannot exceed 200 characters.',

            // Email templates validation
            'acceptance_email_template.max' => 'Acceptance email template cannot exceed 2000 characters.',
            'rejection_email_template.max' => 'Rejection email template cannot exceed 2000 characters.',
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
            'is_public' => $this->boolean('is_public', true),
            'requires_login' => $this->boolean('requires_login', false),
            'show_application_count' => $this->boolean('show_application_count', false),
            'allow_multiple_applications' => $this->boolean('allow_multiple_applications', false),
            'auto_approve' => $this->boolean('auto_approve', false),
        ]);

        if (!$this->has('required_fields') || empty($this->required_fields)) {
            $this->merge([
                'required_fields' => ['bio', 'topic_title', 'topic_description', 'experience_level']
            ]);
        }

        if ($this->has('custom_questions') && is_array($this->custom_questions)) {
            $questions = collect($this->custom_questions)->map(function ($question) {
                $cleaned = [
                    'question' => $question['question'] ?? '',
                    'type' => $question['type'] ?? 'text',
                    'required' => (bool) ($question['required'] ?? false),
                ];

                if (isset($question['id'])) {
                    $cleaned['id'] = $question['id'];
                }

                if (in_array($cleaned['type'], ['select', 'radio', 'checkbox'])) {
                    $cleaned['options'] = array_filter($question['options'] ?? []);
                }

                return $cleaned;
            })->filter(function ($question) {
                return !empty($question['question']);
            })->values()->toArray();

            $this->merge(['custom_questions' => $questions]);
        }

        if ($this->has('acceptance_email_template')) {
            $this->merge([
                'acceptance_email_template' => trim($this->acceptance_email_template) ?: null
            ]);
        }

        if ($this->has('rejection_email_template')) {
            $this->merge([
                'rejection_email_template' => trim($this->rejection_email_template) ?: null
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cfs = $this->route('cfs');

            if (in_array($this->application_type, ['event', 'both'])) {
                $community = $this->route('community');
                $hasEvents = $community->events()->where('status', '!=', 'cancelled')->exists();

                if (!$hasEvents) {
                    $validator->errors()->add('application_type',
                        'Cannot accept event applications - no events available in this community.'
                    );
                }
            }

            $currentStatus = $cfs->status;
            $newStatus = $this->status;

            if ($currentStatus === 'archived' && $newStatus !== 'archived') {
                $validator->errors()->add('status',
                    'Cannot reopen an archived call for speakers.'
                );
            }

            if ($currentStatus === 'open' && $newStatus === 'closed') {
                $pendingApplications = $cfs->applications()->where('status', 'pending')->count();
                if ($pendingApplications > 0) {
                    $validator->errors()->add('status',
                        "Cannot close CFS with {$pendingApplications} pending applications. Please review them first."
                    );
                }
            }

            if ($this->has('closes_at') && $this->closes_at) {
                if ($newStatus === 'open' && $this->closes_at < now()) {
                    $validator->errors()->add('closes_at',
                        'Cannot set closing date in the past for an open call for speakers.'
                    );
                }
            }

            if ($this->has('custom_questions')) {
                $existingApplications = $cfs->applications()->count();
                if ($existingApplications > 0) {
                    $currentQuestions = collect($cfs->custom_questions ?? []);
                    $newQuestions = collect($this->custom_questions ?? []);

                    $removedQuestions = $currentQuestions->whereNotIn('id', $newQuestions->pluck('id'));
                    if ($removedQuestions->isNotEmpty()) {
                        $validator->errors()->add('custom_questions',
                            'Cannot remove custom questions when applications exist. Consider archiving this CFS and creating a new one.'
                        );
                    }
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
            'opens_at' => 'opening date',
            'closes_at' => 'closing date',
            'show_application_count' => 'show application count',
            'allow_multiple_applications' => 'allow multiple applications',
            'application_type' => 'application type',
            'required_fields' => 'required fields',
            'custom_questions' => 'custom questions',
            'acceptance_email_template' => 'acceptance email template',
            'rejection_email_template' => 'rejection email template',
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Log significant changes for audit purposes
        $cfs = $this->route('cfs');

        if ($cfs->status !== $this->status) {
            \Log::info('CFS status changed', [
                'cfs_id' => $cfs->id,
                'community_id' => $cfs->community_id,
                'old_status' => $cfs->status,
                'new_status' => $this->status,
                'user_id' => auth()->id(),
            ]);
        }
    }
}
