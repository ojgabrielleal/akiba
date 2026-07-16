<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'action' => $this->action,
            'scheduled_at' => $this->formatScheduledAt(),
            'status' => $this->status,
            'user' => UserResource::make($this->user)->format('summary'),
        ];
    }

    private function formatScheduledAt(): ?string
    {
        return $this->scheduled_at?->setTimezone('America/Sao_Paulo')->format('Y-m-d\TH:i');
    }
}
