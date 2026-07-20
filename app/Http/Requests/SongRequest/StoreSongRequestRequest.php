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
        return [
            'address' => 'sometimes|required|string|max:255',
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
