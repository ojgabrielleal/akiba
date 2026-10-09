<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use App\Models\EnigmaGameInteraction;
use App\Support\AuthenticatedMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnigmaGameResource extends JsonResource
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
        $memberFinalAnswer = $memberInteractions->firstWhere('type', EnigmaGameInteraction::TYPE_FINAL_ANSWER);
        $hasFinalAnswer = $memberFinalAnswer !== null;
        $nextInteractionAt = $lastInteraction && ! $hasFinalAnswer
            ? $lastInteraction->created_at->copy()->addDay()
            : null;
        $correctAnswerInteractions = $this->interactions
            ->filter(fn ($interaction) => $interaction->isCorrectFinalAnswer())
            ->values();
        $solvedInteraction = $correctAnswerInteractions->first();

        $canViewPrivate = $request->user()?->can('view', $this->resource) ?? false;
        $interactions = $canViewPrivate
            ? $this->interactions
            : $this->interactions
                ->where('type', EnigmaGameInteraction::TYPE_QUESTION)
                ->filter(fn ($interaction) => filled($interaction->admin_response) || filled($interaction->result))
                ->values();

        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'content' => $this->content,
            'image' => $this->image,
            'status' => $this->status,
            'solution' => $canViewPrivate || $solvedInteraction ? $this->solution : null,
            'solution_title' => $canViewPrivate || $solvedInteraction ? $this->solution_title : null,
            'solution_image' => $canViewPrivate || $solvedInteraction ? $this->solution_image : null,
            'solution_synopsis' => $canViewPrivate || $solvedInteraction ? $this->solution_synopsis : null,
            'created_at' => $this->created_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            'author' => $this->author ? UserResource::make($this->author)->format('summary') : null,
            'solved' => $solvedInteraction !== null,
            'correct_answers_count' => $correctAnswerInteractions->count(),
            'solved_by' => $solvedInteraction ? [
                'uuid' => $solvedInteraction->participant?->uuid,
                'name' => $solvedInteraction->participant?->nickname
                    ?? $solvedInteraction->participant?->name
                    ?? $solvedInteraction->participant?->username,
                'avatar' => $solvedInteraction->participant?->avatar,
                'gender' => $solvedInteraction->participant?->gender,
            ] : null,
            'solved_at' => $solvedInteraction?->responded_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            'interactions' => EnigmaGameInteractionResource::collection($interactions),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'publish' => $request->user()?->can('publish', $this->resource) ?? false,
                'respond' => $request->user()?->can('respond', \App\Models\EnigmaGame::class) ?? false,
            ],
            'participation' => [
                'can_interact' => $member !== null
                    && ! $hasFinalAnswer
                    && ($nextInteractionAt === null || $nextInteractionAt->isPast()),
                'next_interaction_at' => $nextInteractionAt?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                'has_submitted_final_answer' => $hasFinalAnswer,
                'final_answer_result' => $memberFinalAnswer?->result,
            ],
        ];
    }
}
