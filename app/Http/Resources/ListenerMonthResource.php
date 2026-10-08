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
            'badges' => $this->badges(),
        ];
    }

    private function badges(): array
    {
        if (! $this->oauthAccount) {
            return [];
        }

        return $this->oauthAccount
            ->badgeAssignments()
            ->active()
            ->with('badge')
            ->latest('acquired_at')
            ->get()
            ->map(fn ($assignment) => [
                'uuid' => $assignment->badge?->uuid,
                'name' => $assignment->badge?->name,
                'code' => $assignment->badge?->code,
                'image' => $assignment->badge?->image,
                'type' => $assignment->badge?->type,
                'acquired_at' => $assignment->acquired_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ])
            ->filter(fn (array $badge) => filled($badge['uuid']))
            ->values()
            ->all();
    }
}
