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
            'ip_address' => $this->ip_address,
            'name' => $this->name,
            'address' => $this->address,
            'message' => $this->message,
            'music' => MusicResource::make($this->music),
            'created_at' => $this->created_at->setTimezone('America/Sao_Paulo')->format('H:i'),
        ];
    }
}
