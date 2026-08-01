<?php

namespace App\Http\Resources\Poll;

use App\Support\AuthenticatedMember;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
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
            'status' => $this->status,
            'question' => $this->question,
            'expires_at' => $this->expires_at?->setTimezone('America/Sao_Paulo')->format('Y-m-d\TH:i'),
            'options' => PollOptionResource::collection($this->options),
            'total_votes' => $this->votes_count,
            'has_voted' => $this->hasVoteFrom($request),
            'is_valid' => $this->isValid(),
        ];
    }

    private function isValid(): bool
    {
        return $this->is_active && (
            $this->expires_at === null || $this->expires_at->isFuture()
        );
    }

    private function hasVoteFrom(Request $request): bool
    {
        $voter = AuthenticatedMember::fromRequest($request);

        if (!$voter) return false;

        return $this->votes->contains(
            fn ($vote) => $vote->voter_type === $voter->getMorphClass()
                && $vote->voter_id === $voter->getKey()
        );
    }
}
