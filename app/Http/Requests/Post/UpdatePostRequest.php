<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\LoggedWebRequest;

class UpdatePostRequest extends LoggedWebRequest
{
    private function isDraft(): bool
    {
        return $this->input('module') === 'review'
            ? $this->input('review.status') === 'draft'
            : $this->input('status') === 'draft';
    }

    private function requiredUnlessDraft(): string
    {
        return $this->isDraft() ? 'nullable' : 'required';
    }

    protected function prepareForValidation(): void
    {
        $post = $this->route('post');

        $this->merge([
            'module' => $this->input('module', $post?->module ?? 'post'),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('post')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requiredUnlessDraft = $this->requiredUnlessDraft();

        return [
            'module' => 'nullable|in:post,review,event',
            'status' => 'required_unless:module,review|nullable|string',
            'title' => "{$requiredUnlessDraft}|string",
            'image' => 'nullable',
            'cover' => 'nullable',
            'references' => "{$requiredUnlessDraft}|array",
            'references.*.uuid' => 'nullable|string',
            'references.*.name' => "{$requiredUnlessDraft}|string|max:255",
            'references.*.url' => "{$requiredUnlessDraft}|url|max:255",
            'tags' => "{$requiredUnlessDraft}|array",
            'tags.*.uuid' => 'nullable|string',
            'tags.*.name' => "{$requiredUnlessDraft}|string|max:255",
            'content' => $this->isDraft() ? 'nullable|string' : 'required_unless:module,review|nullable|string',
            'review' => 'required_if:module,review|nullable|array',
            'review.uuid' => 'nullable|string',
            'review.status' => 'required_if:module,review|string',
            'review.content' => $this->isDraft() ? 'nullable|string' : 'required_if:module,review|string',
            'metadata' => $this->isDraft() ? 'nullable|array' : 'required_unless:module,post|nullable|array',
            'metadata.dates' => $this->isDraft() ? 'nullable|string' : 'required_if:module,event|string',
            'metadata.event_date' => $this->isDraft() ? 'nullable|date' : 'required_if:module,event|date',
            'metadata.address' => $this->isDraft() ? 'nullable|string' : 'required_if:module,event|string',
            'metadata.date_of_release' => $this->isDraft() ? 'nullable|date' : 'required_if:module,review|date',
            'metadata.sinopse' => $this->isDraft() ? 'nullable|string' : 'required_if:module,review|string',
        ];
    }
}
