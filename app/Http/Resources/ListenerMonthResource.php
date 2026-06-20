<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListenerMonthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'avatar' => $this->avatar ?? '/img/defaults/avatar.webp',
            'name' => $this->name,
            'address' => $this->address,
            'birthday' => $this->birthday->format('Y-m-d'),
            'favorite_program' => $this->favorite_program,
            'favorite_music' => $this->favorite_music,
            'requests_total' => $this->requests_total,
        ];
    }
}
