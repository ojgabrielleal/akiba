<?php

namespace App\Http\Requests\Web\Locution;

use App\Http\Requests\Web\LoggedWebRequest;

class FinishLocutionRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('locution.finish') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
