<?php

namespace App\Http\Requests\EnigmaGame;

use App\Models\EnigmaGame;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEnigmaGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $enigmagame = $this->route('enigmagame');

        if (! $user?->can('update', $enigmagame)) {
            return false;
        }

        return $this->input('status') !== EnigmaGame::STATUS_ACTIVE
            || $user->can('publish', $enigmagame);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', 'in:draft,active,ended,inactive'],
            'solution' => ['nullable', 'string'],
        ];
    }
}
