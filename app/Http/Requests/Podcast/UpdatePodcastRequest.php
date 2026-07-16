<?php

namespace App\Http\Requests\Podcast;

use App\Http\Requests\LoggedWebRequest;

class UpdatePodcastRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('podcast')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'nullable',
            'season' => 'required|integer|min:1',
            'episode' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'description' => 'required|string',
            'audio' => 'required|url',
        ];
    }
}
