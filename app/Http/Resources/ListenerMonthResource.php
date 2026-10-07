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
        $topAnime = $this->top_anime ?? [];

        return [
            'uuid' => $this->uuid,
            'oauth_account_id' => $this->oauthAccount?->uuid,
            'avatar' => $this->oauthAccount?->avatar,
            'name' => $this->oauthAccount?->nickname,
            'address' => $this->oauthAccount?->address,
            'birth_date' => $this->oauthAccount?->birth_date?->format('Y-m-d'),
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
            'top_anime' => [
                'anime_theme_list_id' => $topAnime['anime_theme_list_id'] ?? null,
                'slug' => $topAnime['slug'] ?? null,
                'name' => $topAnime['name'] ?? null,
                'image' => $topAnime['image'] ?? null,
                'metadata' => $topAnime['metadata'] ?? null,
            ],
            'requests_total' => $this->requests_total,
        ];
    }
}
