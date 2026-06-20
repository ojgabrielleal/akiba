<?php

namespace App\Http\Requests\Web\Administration;

use App\Http\Requests\Web\LoggedWebRequest;

class UpdateTaskRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('task')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user' => 'required|exists:users,uuid',
            'title' => 'required|string|max:255',
            'dead_line' => 'required|date',
            'description' => 'required|string',
        ];
    }
}
