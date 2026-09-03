<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use App\Models\MysteryInteraction;
use App\Support\AuthenticatedMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MysteryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $member = AuthenticatedMember::fromRequest($request);
        $memberInteractions = $member
            ? $this->interactions
                ->where('participant_type', $member->getMorphClass())
                ->where('participant_id', $member->getKey())
            : collect();
        $lastInteraction = $memberInteractions->sortByDesc('created_at')->first();
        $memberFinalAnswer = $memberInteractions->firstWhere('type', MysteryInteraction::TYPE_FINAL_ANSWER);
        $hasFinalAnswer = $memberFinalAnswer !== null;
        $nextInteractionAt = $lastInteraction && ! $hasFinalAnswer
            ? $lastInteraction->created_at->copy()->addDay()
            : null;
        $solvedInteraction = $this->interactions
            ->first(fn ($interaction) => $interaction->type === MysteryInteraction::TYPE_FINAL_ANSWER
                && $interaction->result === 'correct');

        $canViewPrivate = $request->user()?->can('view', $this->resource) ?? false;
        $interactions = $canViewPrivate
            ? $this->interactions
            : $this->interactions
                ->where('type', MysteryInteraction::TYPE_QUESTION)
                ->whereNotNull('admin_response')
                ->values();

        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'solution' => $canViewPrivate || $solvedInteraction ? $this->solution : null,
            'created_at' => $this->created_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            'author' => $this->author ? UserResource::make($this->author)->format('summary') : null,
            'solved' => $solvedInteraction !== null,
            'solved_by' => $solvedInteraction ? [
                'uuid' => $solvedInteraction->participant?->uuid,
                'name' => $solvedInteraction->participant?->nickname
                    ?? $solvedInteraction->participant?->name
                    ?? $solvedInteraction->participant?->username,
                'avatar' => $solvedInteraction->participant?->avatar,
                'gender' => $solvedInteraction->participant?->gender,
            ] : null,
            'solved_at' => $solvedInteraction?->responded_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            'interactions' => MysteryInteractionResource::collection($interactions),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'publish' => $request->user()?->can('publish', $this->resource) ?? false,
                'respond' => $request->user()?->can('respond', \App\Models\Mystery::class) ?? false,
            ],
            'participation' => [
                'can_interact' => $member !== null
                    && ! $solvedInteraction
                    && ! $hasFinalAnswer
                    && ($nextInteractionAt === null || $nextInteractionAt->isPast()),
                'next_interaction_at' => $nextInteractionAt?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                'has_submitted_final_answer' => $hasFinalAnswer,
                'final_answer_result' => $memberFinalAnswer?->result,
            ],
        ];
    }
}
