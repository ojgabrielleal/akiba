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

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.image' => 'O arquivo precisa ser uma imagem.',
            'caption.string' => 'A legenda precisa ser um texto.',
            'caption.max' => 'A legenda deve ter no máximo 255 caracteres.',
            'listener_name.string' => 'O nome do ouvinte precisa ser um texto.',
            'listener_name.max' => 'O nome do ouvinte deve ter no máximo 255 caracteres.',
        ];
    }
}
