<?php

namespace App\Http\Resources\Onair;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnairStreamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'program' => [
                'name' => $this->program?->name,
            ],
            'host' => [
                'name' => $this->program?->host?->nickname,
            ],
            'current_song' => [
                'music' => $this->streaming_data['current_song']['music'] ?? null,
                'cover' => $this->streaming_data['current_song']['cover'] ?? null,
            ],
        ];
    }
}
