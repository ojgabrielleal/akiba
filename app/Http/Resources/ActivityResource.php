<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'allows_confirmations' => $this->allows_confirmations,
            'title' => $this->title,
            'content' => $this->content,
            'limit' => $this->limit->format('Y-m-d'),
            'hour' => $this->calendar?->hour->format('H:i'),
            'date' => $this->calendar?->date->format('Y-m-d'),
            'author' => UserResource::make($this->author)->format('compact'),
            'confirmations' => UserResource::collection($this->confirmations),
        ];
    }
}
