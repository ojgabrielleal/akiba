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

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Selecione uma imagem.',
            'image.image' => 'O arquivo precisa ser uma imagem.',
            'caption.string' => 'A legenda precisa ser um texto.',
            'caption.max' => 'A legenda deve ter no máximo 255 caracteres.',
            'listener_name.string' => 'O nome do ouvinte precisa ser um texto.',
            'listener_name.max' => 'O nome do ouvinte deve ter no máximo 255 caracteres.',
        ];
    }
}
