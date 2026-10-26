<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\LoggedWebRequest;
use App\Http\Requests\Post\Concerns\HandlesSocialInput;
use App\Rules\RespectfulContent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends LoggedWebRequest
{
    use HandlesSocialInput;

    private function operationStatus(): ?string
    {
        $post = $this->route('post');

        if ($this->input('module') === 'review') {
            return $this->input('review.status', $post?->status);
        }

        return $this->input('status', $post?->status);
    }

    protected function isDraft(): bool
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
        $this->prepareSocialForValidation();

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
            'tags' => $this->input('module', $post?->module ?? 'post') === 'event'
                ? [['name' => 'event']]
                : $this->input('tags', []),
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
        $user = $this->user();
        $post = $this->route('post');

        if (! ($user?->can('update', $post) ?? false)) {
            return false;
        }

        if ($post->status === 'published' || $this->operationStatus() !== 'published') {
            return true;
        }

        return $user->hasPermission('post.publish')
            || ($post->status === 'revision' && $user->can('approve', $post));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $post = $this->route('post');
        $requiredUnlessDraft = $this->requiredUnlessDraft();
        $socialRules = ! $this->has('social') && is_array($post?->social)
            ? []
            : $this->socialRules();

        return [
            'module' => 'required|in:post,review,event',
            'status' => 'required_unless:module,review|nullable|string|in:published,revision,draft',
            'title' => [$requiredUnlessDraft, 'string', new RespectfulContent],
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
            'content' => [$this->isDraft() ? 'nullable' : 'required_unless:module,review', 'nullable', 'string', new RespectfulContent],
            'studio' => "exclude_unless:module,review|{$requiredUnlessDraft}|string|max:255",
            'review' => 'exclude_unless:module,review|required_if:module,review|nullable|array',
            'review.uuid' => 'exclude_unless:module,review|nullable|string',
            'review.status' => 'exclude_unless:module,review|nullable|string|in:published,revision,draft',
            'review.content' => [$this->isDraft() ? 'exclude_unless:module,review' : 'exclude_unless:module,review', $this->isDraft() ? 'nullable' : 'required_if:module,review', 'string', new RespectfulContent],
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
            'metadata.sinopse' => [$this->isDraft() ? 'exclude_unless:module,review' : 'exclude_unless:module,review', $this->isDraft() ? 'nullable' : 'required_if:module,review', 'string', new RespectfulContent],
            ...$socialRules,
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Esse campo é obrigatório.',
            'required_if' => 'Esse campo é obrigatório.',
            'required_unless' => 'Esse campo é obrigatório.',
            'image.uploaded' => 'A imagem não foi enviada corretamente. Tente uma imagem menor ou em outro formato.',
            'cover.uploaded' => 'A capa não foi enviada corretamente. Tente uma imagem menor ou em outro formato.',
            'cover.image' => 'A capa precisa ser uma imagem.',
            'cover.dimensions' => 'A capa precisa ter pelo menos 1200x400 pixels para funcionar bem como fundo.',
            ...$this->socialMessages(),
        ];
    }
}
