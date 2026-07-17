<?php

namespace App\Http\Requests\Calendar;

use App\Http\Requests\LoggedWebRequest;

use App\Models\Calendar;

class StoreCalendarRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Calendar::class) ?? false;
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
            'content' => 'required|string',
            'hour' => 'required|date_format:H:i',
            'type' => 'required|string',
            'date' => 'required|date',
        ];
    }
}
