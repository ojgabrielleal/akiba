<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\LoggedWebRequest;

use App\Models\Task;

class StoreTaskRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
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
