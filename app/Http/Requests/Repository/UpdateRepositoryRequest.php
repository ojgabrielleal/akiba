<?php

namespace App\Http\Requests\Repository;

use App\Http\Requests\LoggedWebRequest;
use App\Models\Repository;
use Illuminate\Validation\Validator;

class UpdateRepositoryRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('repository')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'url' => [
                'required',
                'url',
                'max:255',
            ],
            'image' => ['nullable', 'image'],
            'type' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $nameConflict = $this->repositoryConflict('name');
            $urlConflict = $this->repositoryConflict('url');

            if ($this->hasInactiveConflict($nameConflict, $urlConflict)) {
                $this->addInactiveConflictErrors($validator);

                return;
            }

            if ($nameConflict) {
                $validator->errors()->add('name', 'Esse arquivo já existe.');
            }

            if ($urlConflict) {
                $validator->errors()->add('url', 'Esse endereço de download já existe.');
            }
        });
    }

    private function repositoryConflict(string $field): ?Repository
    {
        $current = $this->route('repository');

        return Repository::where($field, $this->input($field))
            ->whereKeyNot($current?->getKey())
            ->first();
    }

    private function hasInactiveConflict(?Repository ...$repositories): bool
    {
        return collect($repositories)
            ->filter()
            ->contains(fn (Repository $repository) => ! $repository->is_active);
    }

    private function addInactiveConflictErrors(Validator $validator): void
    {
        $validator->errors()->add('name', 'Esse item já existe e está desativado.');
        $validator->errors()->add('url', 'Esse endereço já existe e está desativado.');
    }
}
