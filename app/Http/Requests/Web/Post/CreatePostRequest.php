<?php

namespace App\Http\Requests\Web\Post;

use App\Http\Requests\Web\LoggedWebRequest;
use App\Models\Post;

class CreatePostRequest extends LoggedWebRequest
{
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
        return [
            'module' => 'required|in:post,review,event',
            'status' => 'required_unless:module,review|nullable|string',
            'title' => 'required',
            'image' => 'required',
            'cover' => 'required',
            'references' => 'required|array',
            'references.*.name' => 'required|string|max:255',
            'references.*.url' => 'required|url|max:255',
            'tags' => 'required|array',
            'tags.*.name' => 'required|string|max:255',
            'content' => 'required_unless:module,review|nullable|string',
            'metadata' => 'required_unless:module,post|nullable|array',
            'metadata.dates' => 'required_if:module,event|string',
            'metadata.address' => 'required_if:module,event|string',
            'metadata.year_of_release' => 'required_if:module,review|integer',
            'metadata.sinopse' => 'required_if:module,review|string',
            'review' => 'required_if:module,review|nullable|array',
            'review.status' => 'required_if:module,review|string',
            'review.content' => 'required_if:module,review|string',
        ];
    }
}
