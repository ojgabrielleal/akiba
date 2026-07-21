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
            'avatar' => $this->oauthAccount?->avatar,
            'name' => $this->oauthAccount?->nickname,
            'address' => $this->oauthAccount?->address,
            'birth_date' => $this->oauthAccount?->birth_date?->format('Y-m-d'),
            'favorite_program' => [
                'name' => $favoriteProgram['name'],
                'image' => $favoriteProgram['image'],
            ],
            'favorite_music' => [
                'name' => $favoriteMusic['name'],
                'artist' => $favoriteMusic['artist'],
                'production' => $favoriteMusic['production'],
                'image' => $favoriteMusic['image'],
            ],
            'requests_total' => $this->requests_total,
        ];
    }
}
