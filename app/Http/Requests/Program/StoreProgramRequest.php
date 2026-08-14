<?php

namespace App\Http\Requests\Program;

use App\Http\Requests\LoggedWebRequest;

use App\Models\Program;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreProgramRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Program::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user' => [
                Rule::requiredIf(fn () => in_array($this->input('execution_mode'), ['auto_dj', 'scheduled', 'playlist'], true)
                    || (
                        $this->input('execution_mode') === 'live'
                        && $this->input('access_type') === 'private'
                    )),
                'nullable',
                'exists:users,uuid',
            ],
            'name' => ['required', 'string', 'max:255'],
            'image' => 'required|image',
            'access_type' => 'required_unless:execution_mode,auto_dj|nullable|in:free,private',
            'execution_mode' => 'required|in:live,scheduled,playlist,auto_dj',
            'is_default_auto_dj' => 'nullable|boolean',
            'airtimes' => 'nullable|array',
            'airtimes.*.day' => 'required_with:airtimes|integer|min:0|max:6',
            'airtimes.*.hour' => 'required_with:airtimes|date_format:H:i,H:i:s',
            'schedules' => [
                Rule::requiredIf(fn () => in_array($this->input('execution_mode'), ['scheduled', 'playlist', 'auto_dj'], true)),
                'array',
                'min:1',
            ],
            'schedules.*.scheduled_at' => 'required_with:schedules|date',
            'phrases' => [
                Rule::requiredIf(fn () => in_array($this->input('execution_mode'), ['scheduled', 'playlist', 'auto_dj'], true)),
                'array',
                'min:1',
            ],
            'phrases.*.icon' => 'nullable|string',
            'phrases.*.text' => 'required_with:phrases|string',
            'phrases.*.decoration' => 'nullable|string',
            'phrases.*.texture' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'phrases.required' => 'Adicione pelo menos uma frase.',
            'phrases.min' => 'Adicione pelo menos uma frase.',
            'schedules.required' => 'Adicione pelo menos um horário de agendamento.',
            'schedules.min' => 'Adicione pelo menos um horário de agendamento.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $program = Program::where('name', $this->input('name'))->first();

            if (! $program) {
                return;
            }

            $validator->errors()->add(
                'name',
                $program->is_active
                    ? 'Esse programa já existe.'
                    : 'Esse item já existe e está desativado.'
            );
        });
    }
}
