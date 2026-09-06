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

    private function coverRules(): array
    {
        $post = $this->route('post');

        return [
            Rule::requiredIf(fn () => ! $this->isDraft() && blank($post?->cover)),
            'nullable',
            Rule::when($this->hasFile('cover'), [
                'image',
                'dimensions:min_width=1200,min_height=400',
            ]),
        ];
    }

    protected function prepareForValidation(): void
    {
        $post = $this->route('post');

        $this->merge([
            'module' => $this->input('module', $post?->module ?? 'post'),
            'title' => $this->stringInput($this->input('title')),
            'content' => $this->emptyHtmlToNull($this->stringInput($this->input('content'))),
            'studio' => $this->stringInput($this->input('studio')),
            'metadata' => [
                ...$this->input('metadata', []),
                'dates' => $this->stringInput($this->input('metadata.dates')),
                'event_date' => $this->stringInput($this->input('metadata.event_date')),
                'address' => $this->stringInput($this->input('metadata.address')),
                'date_of_release' => $this->stringInput($this->input('metadata.date_of_release')),
                'sinopse' => $this->emptyHtmlToNull($this->stringInput($this->input('metadata.sinopse'))),
            ],
            'review' => [
                ...$this->input('review', []),
                'content' => $this->emptyHtmlToNull($this->stringInput($this->input('review.content'))),
            ],
        ]);
    }

    private function stringInput(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        return collect($value)
            ->flatten()
            ->filter(fn (mixed $item) => is_scalar($item) && filled((string) $item))
            ->last();
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
            'cover' => $this->coverRules(),
            'references' => 'exclude_if:module,review|nullable|array',
            'references.*.uuid' => 'exclude_if:module,review|nullable',
            'references.*.name' => 'exclude_if:module,review|nullable',
            'references.*.url' => 'exclude_if:module,review|nullable',
            'tags' => "exclude_if:module,review|{$requiredUnlessDraft}|array",
            'tags.*.uuid' => 'exclude_if:module,review|nullable|string',
            'tags.*.name' => "exclude_if:module,review|{$requiredUnlessDraft}|string|max:255",
            'content' => $this->isDraft() ? 'nullable|string' : 'required_unless:module,review|nullable|string',
            'studio' => "exclude_unless:module,review|{$requiredUnlessDraft}|string|max:255",
            'review' => 'exclude_unless:module,review|required_if:module,review|nullable|array',
            'review.uuid' => 'exclude_unless:module,review|nullable|string',
            'review.status' => 'exclude_unless:module,review|nullable|string|in:published,revision,draft',
            'review.content' => $this->isDraft()
                ? 'exclude_unless:module,review|nullable|string'
                : 'exclude_unless:module,review|required_if:module,review|string',
            'metadata' => 'exclude_if:module,post|nullable|array',
            'metadata.dates' => $this->isDraft()
                ? 'exclude_unless:module,event|nullable|string'
                : 'exclude_unless:module,event|required_if:module,event|string',
            'metadata.event_date' => $this->isDraft()
                ? 'exclude_unless:module,event|nullable|date'
                : 'exclude_unless:module,event|required_if:module,event|date',
            'metadata.address' => $this->isDraft()
                ? 'exclude_unless:module,event|nullable|string'
                : 'exclude_unless:module,event|required_if:module,event|string',
            'metadata.date_of_release' => $this->isDraft()
                ? 'exclude_unless:module,review|nullable|date'
                : 'exclude_unless:module,review|required_if:module,review|date',
            'metadata.sinopse' => $this->isDraft()
                ? 'exclude_unless:module,review|nullable|string'
                : 'exclude_unless:module,review|required_if:module,review|string',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Esse campo é obrigatório.',
            'required_if' => 'Esse campo é obrigatório.',
            'required_unless' => 'Esse campo é obrigatório.',
            'cover.image' => 'A capa precisa ser uma imagem.',
            'cover.dimensions' => 'A capa precisa ter pelo menos 1200x400 pixels para funcionar bem como fundo.',
        ];
    }
}
