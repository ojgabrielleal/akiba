<?php

namespace App\Http\Requests\SongRequest;

use App\Http\Requests\LoggedWebRequest;

class StoreSongRequestRequest extends LoggedWebRequest
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
        $oauthAccount = $this->attributes->get('oauth_account');
        $profileIncomplete = $oauthAccount?->profile_completed_at === null;

        return [
            'address' => [$profileIncomplete ? 'required' : 'nullable', 'string', 'max:255'],
            'birth_date' => [$profileIncomplete ? 'required' : 'nullable', 'date', 'before:today'],
            'anime' => 'required|string',
            'music' => 'required|array',
            'music.production' => 'required|string',
            'music.type' => 'required|string',
            'music.artist' => 'required|string',
            'music.name' => 'required|string',
            'music.image' => 'nullable|string',
            'message' => 'nullable|string',
        ];
    }
}
