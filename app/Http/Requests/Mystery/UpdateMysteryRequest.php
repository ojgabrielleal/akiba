<?php

namespace App\Http\Requests\Mystery;

use App\Models\Mystery;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMysteryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $mystery = $this->route('mystery');

        if (! $user?->can('update', $mystery)) {
            return false;
        }

        return $this->input('status') !== Mystery::STATUS_ACTIVE
            || $user->can('publish', $mystery);
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
