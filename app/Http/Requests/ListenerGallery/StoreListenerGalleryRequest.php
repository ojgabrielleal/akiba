<?php

namespace App\Http\Requests\ListenerGallery;

use App\Http\Requests\LoggedWebRequest;

use App\Models\ListenerGallery;

class StoreListenerGalleryRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', ListenerGallery::class) ?? false;
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
            'caption' => 'nullable|string|max:255',
            'listener_name' => 'nullable|string|max:255',
        ];
    }
}
