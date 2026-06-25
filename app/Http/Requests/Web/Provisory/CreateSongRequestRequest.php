<?php

namespace App\Http\Requests\Web\Provisory;

use App\Http\Requests\Web\LoggedWebRequest;

class CreateSongRequestRequest extends LoggedWebRequest
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
            'name' => 'required|string',
            'address' => 'required|string',
            'anime' => 'required|string',
            'music' => 'required|array',
            'music.production' => 'required|string',
            'music.type' => 'required|string',
            'music.artist' => 'required|string',
            'music.name' => 'required|string',
            'music.image' => 'nullable|string',
        ];
    }
}
