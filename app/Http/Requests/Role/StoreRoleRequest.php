<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\LoggedWebRequest;

use App\Models\Role;

class StoreRoleRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Role::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => 'required|string|max:255|unique:roles,label',
            'public_label' => 'nullable|string|max:255',
            'weight' => 'required|integer',
            'description' => 'nullable|string',
            'icon' => 'required|image|mimes:png,jpg,jpeg,webp|max:1024',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,uuid',
        ];
    }
}
