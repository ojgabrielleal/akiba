<?php

namespace App\Http\Requests\Web\Administration;

use App\Http\Requests\Web\LoggedWebRequest;
use App\Models\User;

class CreateUserRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_virtual' => 'required|boolean',
            'username' => 'required_if:is_virtual,false|nullable|string|max:255|unique:users,username',
            'password' => 'required_if:is_virtual,false|nullable|string|min:6',
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ];
    }
}
