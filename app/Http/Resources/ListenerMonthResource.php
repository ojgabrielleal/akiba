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
        $favoriteProgram = $this->favorite_program ?? [];
        $favoriteMusic = $this->favorite_music ?? [];

        return [
            'uuid' => $this->uuid,
            'avatar' => $this->avatar ?? '/img/defaults/avatar.webp',
            'name' => $this->name,
            'address' => $this->address,
            'birthday' => $this->birthday?->format('Y-m-d'),
            'favorite_program' => [
                'name' => $favoriteProgram['name'] ?? null,
                'image' => $favoriteProgram['image'] ?? null,
            ],
            'favorite_music' => [
                'name' => $favoriteMusic['name'] ?? null,
                'artist' => $favoriteMusic['artist'] ?? null,
                'production' => $favoriteMusic['production'] ?? null,
                'image' => $favoriteMusic['image'] ?? null,
            ],
            'requests_total' => $this->requests_total,
        ];
    }
}
