<?php

namespace App\Http\Requests\Music;

use App\Http\Requests\LoggedWebRequest;

class UpdateMusicRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('music')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string|max:255',
            'production' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image',
            'image_ranking' => 'nullable|image',
        ];
    }
}
