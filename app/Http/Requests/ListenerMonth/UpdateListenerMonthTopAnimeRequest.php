<?php

namespace App\Http\Requests\ListenerMonth;

use App\Http\Requests\LoggedWebRequest;

class UpdateListenerMonthTopAnimeRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'top_anime' => ['required', 'array'],
            'top_anime.anime_theme_list_id' => ['nullable', 'string', 'max:255'],
            'top_anime.slug' => ['nullable', 'string', 'max:255'],
            'top_anime.name' => ['required', 'string', 'max:255'],
            'top_anime.image' => ['nullable', 'url', 'max:2048'],
            'top_anime.metadata' => ['nullable', 'array'],
        ];
    }
}
