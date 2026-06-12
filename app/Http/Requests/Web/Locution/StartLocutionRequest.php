<?php

namespace App\Http\Requests\Web\Locution;

use App\Http\Requests\Web\LoggedWebRequest;

class StartLocutionRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('locution.start') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phrase' => 'required',
            'program' => 'required',
        ];
    }
}
