<?php

namespace App\Http\Requests\Web\Podcast;

use App\Http\Requests\Web\LoggedWebRequest;
use App\Models\Podcast;

class CreatePodcastRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Podcast::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'required|image',
            'season' => 'required|integer|min:1',
            'episode' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'description' => 'required|string',
            'audio' => 'required|url',
        ];
    }
}
