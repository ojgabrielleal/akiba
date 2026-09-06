<?php

namespace App\Http\Requests\EnigmaGame;

use App\Models\EnigmaGame;
use Illuminate\Foundation\Http\FormRequest;

class RespondEnigmaGameInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('respond', EnigmaGame::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'admin_response' => ['nullable', 'string'],
            'result' => ['nullable', 'string', 'in:correct,incorrect'],
        ];
    }
}
