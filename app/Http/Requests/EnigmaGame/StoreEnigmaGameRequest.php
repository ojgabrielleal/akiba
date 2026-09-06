<?php

namespace App\Http\Requests\EnigmaGame;

use App\Models\EnigmaGame;
use Illuminate\Foundation\Http\FormRequest;

class StoreEnigmaGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->can('create', EnigmaGame::class)) {
            return false;
        }

        return $this->input('status') !== EnigmaGame::STATUS_ACTIVE
            || $user->can('publish', new EnigmaGame());
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
