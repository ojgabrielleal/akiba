<?php

namespace App\Http\Requests\Locution;

use App\Http\Requests\LoggedWebRequest;

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
            'send_notification' => 'required|boolean',
        ];
    }
}
