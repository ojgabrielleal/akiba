<?php

namespace App\Http\Requests\SongRequest;

use App\Http\Requests\LoggedWebRequest;
use App\Models\OAuthAccount;

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
        $profileIncomplete = $oauthAccount instanceof OAuthAccount
            && $oauthAccount->profile_completed_at === null;

        return [
            'address' => [$profileIncomplete ? 'required' : 'nullable', 'string', 'max:255'],
            'birth_date' => [$profileIncomplete ? 'required' : 'nullable', 'date', 'before:today'],
            'anime' => 'nullable|string',
            'request_mode' => 'nullable|string|in:music,message',
            'music' => 'required_if:request_mode,music|nullable|array',
            'music.production' => 'required_with:music|string',
            'music.type' => 'nullable|string|in:OP,ED,OVA',
            'music.artist' => 'nullable|string',
            'music.name' => 'required_with:music|string',
            'music.image' => 'nullable|string',
            'music.is_manual' => 'nullable|boolean',
            'message' => 'required_without:music|nullable|string',
        ];
    }
}
