<?php

namespace App\Http\Resources;

use App\Models\OAuthAccount;
use App\Models\User;
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
        $requester = $this->requester;

        return [
            'uuid' => $this->uuid,
            'was_reproduced' => $this->was_reproduced,
            'was_canceled' => $this->was_canceled,
            'name' => $this->requesterName($requester),
            'avatar' => $requester?->avatar,
            'gender' => $requester instanceof User ? $requester->gender : null,
            'address' => $this->requesterAddress($requester),
            'message' => $this->message ?? 'Ouvinte não deixou mensagem',
            'music' => MusicResource::make($this->music),
            'created_at' => $this->created_at->setTimezone('America/Sao_Paulo')->format('H:i'),
        ];
    }

    private function requesterName(User|OAuthAccount|null $requester): ?string
    {
        if ($requester instanceof User) {
            return $requester->nickname ?? $requester->name;
        }

        return $requester?->nickname;
    }

    private function requesterAddress(User|OAuthAccount|null $requester): ?string
    {
        if ($requester instanceof User) {
            return collect([$requester->city, $requester->state])
                ->filter()
                ->join(' - ') ?: null;
        }

        return $requester?->address;
    }
}
