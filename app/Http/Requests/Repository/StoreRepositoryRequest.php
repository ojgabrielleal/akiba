<?php

namespace App\Http\Requests\Repository;

use App\Http\Requests\LoggedWebRequest;

use App\Models\Repository;

class StoreRepositoryRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Repository::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|unique:repositories,name',
            'url' => 'required|unique:repositories,url',
            'image' => 'required',
            'type' => 'required',
        ];
    }
}
