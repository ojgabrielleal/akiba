<?php

namespace App\Http\Requests\Mystery;

use App\Models\Mystery;
use Illuminate\Foundation\Http\FormRequest;

class RespondMysteryInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('respond', Mystery::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'admin_response' => ['nullable', 'string'],
            'result' => ['nullable', 'string', 'in:correct,incorrect'],
        ];
    }
}
