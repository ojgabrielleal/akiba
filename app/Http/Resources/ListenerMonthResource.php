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
            'avatar' => $this->avatar ?? null,
            'name' => $this->name,
            'address' => $this->address,
            'favorite_program' => $this->favorite_program,
            'favorite_anime' => $this->favorite_anime,
            'requests_total' => $this->requests_total,
        ];
    }
}
