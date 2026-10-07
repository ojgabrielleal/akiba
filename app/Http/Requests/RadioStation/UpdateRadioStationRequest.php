<?php

namespace App\Http\Requests\RadioStation;

use App\Http\Requests\LoggedWebRequest;
use App\Models\RadioStation;
use Illuminate\Validation\Rule;

class UpdateRadioStationRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        $radioStation = $this->route('radioStation');

        return $radioStation instanceof RadioStation
            && ($this->user()?->can('update', $radioStation) ?? false);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $radioStation = $this->route('radioStation');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('radio_stations', 'name')->ignore($radioStation?->id),
            ],
            'logo' => ['nullable', 'url', 'max:2048'],
            'website' => ['nullable', 'url', 'max:2048'],
            'endpoint' => ['required', 'url', 'max:2048'],
            'listeners_path' => ['required', 'string', 'max:255'],
        ];
    }
}
