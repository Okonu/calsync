<?php

namespace App\Http\Requests\Community;

use App\Models\Community;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunityRequest extends FormRequest
{
    protected ?Community $resolvedCommunity = null;

    public function authorize(): bool
    {
        $community = $this->getCommunity();

        if (!$community) {
            return false;
        }

        return auth()->check() && auth()->user()->canManageCommunity($community);
    }

    public function rules(): array
    {
        $community = $this->getCommunity();

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('communities', 'name')->ignore($community->id)
            ],
            'slug' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('communities', 'slug')->ignore($community->id)
            ],
            'description' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:255',
            'calendar_email' => 'nullable|email|max:100',
            'destination_calendar_id' => 'nullable|exists:calendars,id',
            'availability_calendars' => 'nullable|array',
            'availability_calendars.*' => 'exists:calendars,id',
            'timezone' => 'required|string|max:50|in:' . implode(',', timezone_identifiers_list()),
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_public' => 'boolean',
            'social_links' => 'nullable|array',
            'social_links.twitter' => 'nullable|url|max:255',
            'social_links.linkedin' => 'nullable|url|max:255',
            'social_links.github' => 'nullable|url|max:255',
            'social_links.website' => 'nullable|url|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Community name is required.',
            'name.unique' => 'A community with this name already exists.',
            'slug.unique' => 'This slug is already taken by another community.',
            'slug.alpha_dash' => 'Slug may only contain letters, numbers, dashes, and underscores.',
            'color.regex' => 'Color must be a valid hexadecimal color code.',
            'logo.image' => 'Logo must be an image file.',
            'logo.max' => 'Logo file size must not exceed 2MB.',
            'logo.mimes' => 'Logo must be a JPEG, PNG, JPG, or GIF file.',
            'timezone.in' => 'Please select a valid timezone.',
            'website.url' => 'Website must be a valid URL.',
            'contact_email.email' => 'Please provide a valid email address.',
            'calendar_email.email' => 'Please provide a valid calendar email address.',
            'social_links.twitter.url' => 'Twitter URL must be a valid URL.',
            'social_links.linkedin.url' => 'LinkedIn URL must be a valid URL.',
            'social_links.github.url' => 'GitHub URL must be a valid URL.',
            'social_links.website.url' => 'Website URL must be a valid URL.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('slug') || empty($this->slug)) {
            if ($this->has('name')) {
                $this->merge([
                    'slug' => \Illuminate\Support\Str::slug($this->name)
                ]);
            }
        }

        $this->merge([
            'is_public' => $this->boolean('is_public', true)
        ]);

        if ($this->has('social_links') && is_array($this->social_links)) {
            $socialLinks = array_filter($this->social_links, function ($value) {
                return !empty($value);
            });

            $this->merge([
                'social_links' => empty($socialLinks) ? null : $socialLinks
            ]);
        }
    }

    /**
     * Get the resolved community instance
     */
    protected function getCommunity(): ?Community
    {
        if ($this->resolvedCommunity === null) {
            $communityIdentifier = $this->route('community');

            $this->resolvedCommunity = Community::where('id', $communityIdentifier)
                ->orWhere('slug', $communityIdentifier)
                ->first();
        }

        return $this->resolvedCommunity;
    }

    /**
     * Get the validation attributes that apply to the request.
     */
    public function attributes(): array
    {
        return [
            'calendar_email' => 'calendar email',
            'destination_calendar_id' => 'destination calendar',
            'availability_calendars' => 'availability calendars',
            'social_links.twitter' => 'Twitter URL',
            'social_links.linkedin' => 'LinkedIn URL',
            'social_links.github' => 'GitHub URL',
            'social_links.website' => 'Website URL',
        ];
    }
}
