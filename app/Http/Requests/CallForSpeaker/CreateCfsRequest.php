<?php

namespace App\Http\Requests\CallForSpeaker;

use Illuminate\Foundation\Http\FormRequest;

class CreateCfsRequest extends FormRequest
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
            'slug' => 'nullable|string|max:50|alpha_dash|unique:calls_for_speakers,slug,NULL,id,community_id,' . $community->id,
            'description' => 'nullable|string|max:2000',
            'guidelines' => 'nullable|string|max:2000',
            'opens_at' => 'nullable|date|after_or_equal:today',
            'closes_at' => 'nullable|date|after:opens_at',
            'is_public' => 'boolean',
            'requires_login' => 'boolean',
            'show_application_count' => 'boolean',
            'allow_multiple_applications' => 'boolean',
            'application_type' => 'required|in:event,session,both',
            'auto_approve' => 'boolean',

            // Required fields configuration
            'required_fields' => 'nullable|array',
            'required_fields.*' => 'in:bio,topic_title,topic_description,topic_outline,experience_level,previous_speaking_experience,phone',

            // Custom questions
            'custom_questions' => 'nullable|array|max:10',
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
            'opens_at.after_or_equal' => 'Opening date cannot be in the past.',
            'closes_at.after' => 'Closing date must be after opening date.',
            'application_type.required' => 'Please select an application type.',
            'application_type.in' => 'Please select a valid application type.',

            // Custom questions validation
            'custom_questions.max' => 'You can add a maximum of 10 custom questions.',
            'custom_questions.*.question.required' => 'Question text is required.',
            'custom_questions.*.question.max' => 'Question text cannot exceed 500 characters.',
            'custom_questions.*.type.required' => 'Question type is required.',
            'custom_questions.*.type.in' => 'Please select a valid question type.',
            'custom_questions.*.options.required_if' => 'Options are required for select, radio, and checkbox questions.',
            'custom_questions.*.options.*.max' => 'Option text cannot exceed 200 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $community = $this->route('community');

        if (!$this->has('slug') && $this->has('title')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->title)
            ]);
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

        if ($this->has('custom_questions')) {
            $questions = collect($this->custom_questions)->map(function ($question) {
                $cleaned = [
                    'question' => $question['question'] ?? '',
                    'type' => $question['type'] ?? 'text',
                    'required' => (bool) ($question['required'] ?? false),
                ];

                if (in_array($cleaned['type'], ['select', 'radio', 'checkbox'])) {
                    $cleaned['options'] = array_filter($question['options'] ?? []);
                }

                return $cleaned;
            })->filter(function ($question) {
                return !empty($question['question']);
            })->values()->toArray();

            $this->merge(['custom_questions' => $questions]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (in_array($this->application_type, ['event', 'both'])) {
                $community = $this->route('community');
                $hasEvents = $community->events()->where('status', '!=', 'cancelled')->exists();

                if (!$hasEvents) {
                    $validator->errors()->add('application_type',
                        'Cannot accept event applications - no events available in this community.'
                    );
                }
            }
        });
    }
}
