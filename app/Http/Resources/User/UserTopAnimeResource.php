<?php

namespace App\Http\Resources\User;

use App\Http\Resources\AnimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTopAnimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $anime = $this->relationLoaded('anime') ? $this->anime : null;

        return [
            'uuid' => $this->uuid,
            'position' => $this->position,
            'anime' => AnimeResource::make($this->whenLoaded('anime')),
            'anime_theme_list_id' => $this->anime_theme_list_id,
            'slug' => $anime?->slug ?? $this->slug,
            'name' => $anime?->name ?? $this->name,
            'image' => $anime?->image ?? $this->image,
            'metadata' => $this->metadata,
        ];
    }
}
