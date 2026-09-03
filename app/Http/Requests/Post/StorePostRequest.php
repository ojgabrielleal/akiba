<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\LoggedWebRequest;

use App\Models\Post;

class StorePostRequest extends LoggedWebRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'content' => $this->emptyHtmlToNull($this->input('content')),
            'metadata' => [
                ...$this->input('metadata', []),
                'sinopse' => $this->emptyHtmlToNull($this->input('metadata.sinopse')),
            ],
            'review' => [
                ...$this->input('review', []),
                'content' => $this->emptyHtmlToNull($this->input('review.content')),
            ],
        ]);
    }

    private function emptyHtmlToNull(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return trim(strip_tags(html_entity_decode($value))) === '' ? null : $value;
    }

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

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Post::class) ?? false;
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
            'module' => 'required|in:post,review,event',
            'status' => 'required_unless:module,review|nullable|string|in:published,revision,draft',
            'title' => "{$requiredUnlessDraft}|string",
            'image' => $requiredUnlessDraft,
            'cover' => $requiredUnlessDraft,
            'references' => 'exclude_if:module,review|nullable|array',
            'references.*.name' => 'exclude_if:module,review|nullable',
            'references.*.url' => 'exclude_if:module,review|nullable',
            'tags' => "exclude_if:module,review|{$requiredUnlessDraft}|array",
            'tags.*.name' => "exclude_if:module,review|{$requiredUnlessDraft}|string|max:255",
            'content' => $this->isDraft() ? 'nullable|string' : 'required_unless:module,review|nullable|string',
            'studio' => "exclude_unless:module,review|{$requiredUnlessDraft}|string|max:255",
            'metadata' => $this->isDraft() ? 'nullable|array' : 'required_unless:module,post|nullable|array',
            'metadata.dates' => $this->isDraft() ? 'nullable|string' : 'required_if:module,event|string',
            'metadata.event_date' => $this->isDraft() ? 'nullable|date' : 'required_if:module,event|date',
            'metadata.address' => $this->isDraft() ? 'nullable|string' : 'required_if:module,event|string',
            'metadata.date_of_release' => $this->isDraft() ? 'nullable|date' : 'required_if:module,review|date',
            'metadata.sinopse' => $this->isDraft() ? 'nullable|string' : 'required_if:module,review|string',
            'review' => 'required_if:module,review|nullable|array',
            'review.status' => 'required_if:module,review|string|in:published,revision,draft',
            'review.content' => $this->isDraft() ? 'nullable|string' : 'required_if:module,review|string',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Esse campo é obrigatório.',
            'required_if' => 'Esse campo é obrigatório.',
            'required_unless' => 'Esse campo é obrigatório.',
        ];
    }
}
