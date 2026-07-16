<?php

namespace App\Http\Requests\ListenerMonth;

use App\Http\Requests\LoggedWebRequest;

class StoreListenerMonthRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('listener.month.set') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => 'required|image',
            'birthday' => 'required',
        ];
    }
}
