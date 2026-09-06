<?php

namespace App\Http\Requests\EnigmaGame;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnigmaGameInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:question,final_answer'],
            'content' => ['required', 'string', 'max:2000'],
        ];
    }
}
