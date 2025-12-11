<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasCommunitiesFeature();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:communities,name',
            'slug' => 'nullable|string|max:50|alpha_dash|unique:communities,slug',
            'description' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:255',
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
            'color.regex' => 'Color must be a valid hexadecimal color code.',
            'logo.image' => 'Logo must be an image file.',
            'logo.max' => 'Logo file size must not exceed 2MB.',
            'timezone.in' => 'Please select a valid timezone.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('slug') && $this->has('name')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name)
            ]);
        }

        if (!$this->has('contact_email')) {
            $this->merge([
                'contact_email' => auth()->user()->email
            ]);
        }

        $this->merge([
            'is_public' => $this->boolean('is_public', true)
        ]);
    }
}
