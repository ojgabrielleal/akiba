<?php

namespace App\Http\Requests\User;

use App\Http\Requests\LoggedWebRequest;

class UpdateUserAccessRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('updateAuthority', $this->route('user')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => 'nullable|string|min:6',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ];
    }
}
