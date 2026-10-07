<?php

namespace App\Http\Requests\RadioStation;

use App\Http\Requests\LoggedWebRequest;
use App\Models\RadioStation;

class StoreRadioStationRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', RadioStation::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:radio_stations,name'],
            'logo' => ['nullable', 'url', 'max:2048'],
            'website' => ['nullable', 'url', 'max:2048'],
            'endpoint' => ['required', 'url', 'max:2048'],
            'listeners_path' => ['required', 'string', 'max:255'],
        ];
    }
}
