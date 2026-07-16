<?php

namespace App\Http\Requests\ListenerGallery;

use App\Http\Requests\LoggedWebRequest;

class UpdateListenerGalleryRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('listenerGallery')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'nullable|image',
            'caption' => 'nullable|string|max:255',
            'listener_name' => 'nullable|string|max:255',
        ];
    }
}
