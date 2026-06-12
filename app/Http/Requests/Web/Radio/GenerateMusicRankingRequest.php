<?php

namespace App\Http\Requests\Web\Radio;

use App\Models\Music;
use App\Http\Requests\Web\LoggedWebRequest;

class GenerateMusicRankingRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('setRanking', Music::class) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
