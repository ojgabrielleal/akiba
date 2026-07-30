<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\LoggedWebRequest;

class UpdateProfileRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) ?? false;
    }

    public function rules(): array
    {
        return [
            'avatar' => 'nullable',
            'is_virtual' => 'nullable|boolean',
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'bibliography' => 'required|string',
            'socials' => 'nullable|array',
            'socials.*.uuid' => 'nullable|string',
            'socials.*.name' => 'required_with:socials|string|max:255',
            'socials.*.url' => 'nullable|url',
            'preferences' => 'nullable|array',
            'preferences.likes' => 'nullable|array',
            'preferences.likes.*.uuid' => 'required_with:preferences.likes|string',
            'preferences.likes.*.content' => 'nullable|string',
            'preferences.unlikes' => 'nullable|array',
            'preferences.unlikes.*.uuid' => 'required_with:preferences.unlikes|string',
            'preferences.unlikes.*.content' => 'nullable|string',
            'favorites' => 'nullable|array',
            'favorites.*.uuid' => 'required_with:favorites|string',
            'favorites.*.name' => 'nullable|string|max:255',
            'favorites.*.image' => 'nullable|string',
            'top_animes' => 'nullable|array|size:3',
            'top_animes.*.position' => 'required_with:top_animes|integer|between:1,3|distinct',
            'top_animes.*.anime_theme_list_id' => 'nullable|string|max:255',
            'top_animes.*.slug' => 'nullable|string|max:255',
            'top_animes.*.name' => 'nullable|string|max:255',
            'top_animes.*.image' => 'nullable|string|max:2048',
            'top_animes.*.metadata' => 'nullable|array',
        ];
    }
}
