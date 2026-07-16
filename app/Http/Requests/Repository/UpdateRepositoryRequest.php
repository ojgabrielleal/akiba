<?php

namespace App\Http\Requests\Repository;

use App\Http\Requests\LoggedWebRequest;

class UpdateRepositoryRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('repository')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'image' => 'nullable|image',
            'type' => 'required|string|max:255',
        ];
    }
}
