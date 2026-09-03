<?php

namespace App\Http\Requests\Mystery;

use App\Models\Mystery;
use Illuminate\Foundation\Http\FormRequest;

class StoreMysteryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->can('create', Mystery::class)) {
            return false;
        }

        return $this->input('status') !== Mystery::STATUS_ACTIVE
            || $user->can('publish', new Mystery());
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', 'in:draft,active,inactive'],
            'solution' => ['nullable', 'string'],
        ];
    }
}
