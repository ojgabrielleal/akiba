<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MusicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $anime = $this->relationLoaded('anime') ? $this->anime : null;

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'type' => $this->type,
            'is_manual' => $this->is_manual,
            'anime' => AnimeResource::make($this->whenLoaded('anime')),
            'image' => $anime?->image,
            'production' => $anime?->name,
            'artist' => $this->artist,
            'ranking' => [
                'image' => $this->image_ranking,
            ],
        ];
    }
}
