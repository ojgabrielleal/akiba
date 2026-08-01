<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\LoggedWebRequest;

class UpdatePostRequest extends LoggedWebRequest
{
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
        return [
            'module' => 'nullable|in:post,review,event',
            'status' => 'required_unless:module,review|nullable|string',
            'title' => 'required',
            'image' => 'nullable',
            'cover' => 'nullable',
            'references' => 'required|array',
            'references.*.uuid' => 'nullable|string',
            'references.*.name' => 'required|string|max:255',
            'references.*.url' => 'required|url|max:255',
            'tags' => 'required|array',
            'tags.*.uuid' => 'nullable|string',
            'tags.*.name' => 'required|string|max:255',
            'content' => 'required_unless:module,review|nullable|string',
            'review' => 'required_if:module,review|nullable|array',
            'review.uuid' => 'required_if:module,review|string',
            'review.status' => 'required_if:module,review|string',
            'review.content' => 'required_if:module,review|string',
            'metadata' => 'required_unless:module,post|nullable|array',
            'metadata.dates' => 'required_if:module,event|string',
            'metadata.event_date' => 'required_if:module,event|date',
            'metadata.address' => 'required_if:module,event|string',
            'metadata.year_of_release' => 'required_if:module,review|integer',
            'metadata.sinopse' => 'required_if:module,review|string',
        ];
    }
}
