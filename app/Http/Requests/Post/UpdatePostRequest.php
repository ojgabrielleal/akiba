<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\LoggedWebRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends LoggedWebRequest
{
    private function operationStatus(): ?string
    {
        $post = $this->route('post');

        if ($this->input('module') === 'review') {
            return $this->input('review.status', $post?->status);
        }

        return $this->input('status', $post?->status);
    }

    private function isDraft(): bool
    {
        return $this->operationStatus() === 'draft';
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
        $post = $this->route('post');
        $requiredUnlessDraft = $this->requiredUnlessDraft();

        return [
            'module' => 'required|in:post,review,event',
            'status' => 'required_unless:module,review|nullable|string|in:published,revision,draft',
            'title' => "{$requiredUnlessDraft}|string",
            'image' => [
                Rule::requiredIf(fn () => ! $this->isDraft() && blank($post?->image)),
                'nullable',
            ],
            'cover' => [
                Rule::requiredIf(fn () => ! $this->isDraft() && blank($post?->cover)),
                'nullable',
            ],
            'references' => 'nullable|array',
            'references.*.uuid' => 'nullable',
            'references.*.name' => 'nullable',
            'references.*.url' => 'nullable',
            'tags' => "exclude_if:module,review|{$requiredUnlessDraft}|array",
            'tags.*.uuid' => 'exclude_if:module,review|nullable|string',
            'tags.*.name' => "exclude_if:module,review|{$requiredUnlessDraft}|string|max:255",
            'content' => $this->isDraft() ? 'nullable|string' : 'required_unless:module,review|nullable|string',
            'studio' => "exclude_unless:module,review|{$requiredUnlessDraft}|string|max:255",
            'review' => 'required_if:module,review|nullable|array',
            'review.uuid' => 'nullable|string',
            'review.status' => 'nullable|string|in:published,revision,draft',
            'review.content' => $this->isDraft() ? 'nullable|string' : 'required_if:module,review|string',
            'metadata' => $this->isDraft() ? 'nullable|array' : 'required_unless:module,post|nullable|array',
            'metadata.dates' => $this->isDraft() ? 'nullable|string' : 'required_if:module,event|string',
            'metadata.event_date' => $this->isDraft() ? 'nullable|date' : 'required_if:module,event|date',
            'metadata.address' => $this->isDraft() ? 'nullable|string' : 'required_if:module,event|string',
            'metadata.date_of_release' => $this->isDraft() ? 'nullable|date' : 'required_if:module,review|date',
            'metadata.sinopse' => $this->isDraft() ? 'nullable|string' : 'required_if:module,review|string',
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
