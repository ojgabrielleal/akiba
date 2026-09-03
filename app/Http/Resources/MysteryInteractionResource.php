<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MysteryInteractionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'content' => $this->content,
            'admin_response' => $this->admin_response,
            'result' => $this->result,
            'created_at' => $this->created_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            'responded_at' => $this->responded_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            'participant' => [
                'uuid' => $this->participant?->uuid,
                'name' => $this->participant?->nickname ?? $this->participant?->name ?? $this->participant?->username,
                'avatar' => $this->participant?->avatar,
                'gender' => $this->participant?->gender,
            ],
            'responder' => $this->responder ? UserResource::make($this->responder)->format('summary')->resolve($request) : null,
        ];
    }
}
