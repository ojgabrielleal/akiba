<?php

namespace App\Http\Requests\Radio;

use Illuminate\Foundation\Http\FormRequest;

class CreateProgramRequest extends FormRequest
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
            'user' => 'required',
            'name' => 'required',
            'image' => 'required',
            'access_type' => 'required',
            'execution_mode' => 'required',
            'is_default_auto_dj' => 'nullable|boolean',
        ];
    }
}
