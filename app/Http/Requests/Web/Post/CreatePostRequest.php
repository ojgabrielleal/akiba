<?php

namespace App\Http\Requests\Web\Post;

use App\Http\Requests\Web\LoggedWebRequest;

class CreatePostRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Post::class) ?? false;
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
            'title' => 'required|string|max:255',
            'image' => 'required',
            'cover' => 'required',
            'references' => 'required|array',
            'references.*.name' => 'required|string|max:255',
            'references.*.url' => 'required|url|max:255',
            'tags' => 'required|array',
            'tags.*.name' => 'required|string|max:255',
            'content' => 'required_unless:module,review|nullable|string',
            'sinopse' => 'required_if:module,review|nullable|string',
            'year_of_release' => 'required_if:module,review|nullable|integer',
            'review' => 'required_if:module,review|nullable|array',
            'review.status' => 'required_if:module,review|string',
            'review.content' => 'required_if:module,review|string',
            'dates' => 'required_if:module,event|nullable|string',
            'address' => 'required_if:module,event|nullable|string',
        ];
    }
}
