<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongRequestResource extends JsonResource
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
            'was_reproduced' => $this->was_reproduced,
            'was_canceled' => $this->was_canceled,
            'name' => $this->oauthAccount?->nickname,
            'address' => $this->oauthAccount?->address ?? collect([
                $this->oauthAccount?->city,
                $this->oauthAccount?->state,
                $this->oauthAccount?->country,
            ])->filter()->join(', '),
            'message' => $this->message ?? 'Ouvinte não deixou mensagem',
            'music' => MusicResource::make($this->music),
            'created_at' => $this->created_at->setTimezone('America/Sao_Paulo')->format('H:i'),
        ];
    }
}
