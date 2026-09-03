<?php

namespace App\Services;

use App\Models\Mystery;
use App\Models\MysteryInteraction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MysteryService
{
    public function __construct(private PushNotificationService $pushNotification) {}

    public function store(User $user, array $data): Mystery
    {
        return DB::transaction(function () use ($user, $data): Mystery {
            $mystery = Mystery::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $data['status'] ?? Mystery::STATUS_DRAFT,
                'solution' => $data['solution'] ?? null,
            ]);

            if ($mystery->status === Mystery::STATUS_ACTIVE) {
                $this->publish($mystery);
            }

            return $mystery;
        });
    }

    public function update(Mystery $mystery, array $data): Mystery
    {
        return DB::transaction(function () use ($mystery, $data): Mystery {
            $mystery->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $data['status'] ?? $mystery->status,
                'solution' => $data['solution'] ?? null,
            ]);

            if ($mystery->status === Mystery::STATUS_ACTIVE) {
                $this->publish($mystery);
            }

            return $mystery;
        });
    }

    public function publish(Mystery $mystery): Mystery
    {
        return DB::transaction(function () use ($mystery): Mystery {
            Mystery::query()->lockForUpdate()->whereKeyNot($mystery->id)->active()->update([
                'status' => Mystery::STATUS_INACTIVE,
            ]);

            $mystery->update(['status' => Mystery::STATUS_ACTIVE]);

            return $mystery;
        });
    }

    public function delete(Mystery $mystery): void
    {
        DB::transaction(fn () => $mystery->delete());
    }

    public function deactivate(Mystery $mystery): Mystery
    {
        return DB::transaction(function () use ($mystery): Mystery {
            $mystery->update(['status' => Mystery::STATUS_INACTIVE]);

            return $mystery;
        });
    }

    public function interact(Mystery $mystery, Model $participant, array $data): MysteryInteraction
    {
        return DB::transaction(function () use ($mystery, $participant, $data): MysteryInteraction {
            $mystery = Mystery::query()->lockForUpdate()->active()->whereKey($mystery->id)->firstOrFail();

            if ($mystery->interactions()
                ->where('type', MysteryInteraction::TYPE_FINAL_ANSWER)
                ->where('result', 'correct')
                ->exists()) {
                throw ValidationException::withMessages([
                    'content' => 'Este enigma ja foi resolvido.',
                ]);
            }

            $query = $mystery->interactions()
                ->lockForUpdate()
                ->where('participant_type', $participant->getMorphClass())
                ->where('participant_id', $participant->getKey());

            if ((clone $query)->where('type', MysteryInteraction::TYPE_FINAL_ANSWER)->exists()) {
                throw ValidationException::withMessages([
                    'content' => 'Sua resposta definitiva ja foi enviada para este enigma.',
                ]);
            }

            $lastInteraction = (clone $query)->latest('created_at')->first();

            if ($lastInteraction && $lastInteraction->created_at->gt(now()->subDay())) {
                throw ValidationException::withMessages([
                    'content' => 'Aguarde 24 horas desde sua ultima interacao neste enigma.',
                ]);
            }

            return $mystery->interactions()->create([
                'participant_type' => $participant->getMorphClass(),
                'participant_id' => $participant->getKey(),
                'type' => $data['type'],
                'content' => $data['content'],
            ]);
        });
    }

    public function respond(MysteryInteraction $interaction, User $responder, array $data): MysteryInteraction
    {
        return DB::transaction(function () use ($interaction, $responder, $data): MysteryInteraction {
            $interaction = MysteryInteraction::query()
                ->with('participant')
                ->lockForUpdate()
                ->whereKey($interaction->id)
                ->firstOrFail();

            $shouldNotify = $interaction->type === MysteryInteraction::TYPE_QUESTION
                && blank($interaction->admin_response)
                && blank($interaction->response_notified_at);

            validator($data, [
                'admin_response' => [
                    Rule::requiredIf($interaction->type === MysteryInteraction::TYPE_QUESTION),
                    'nullable',
                    'string',
                ],
                'result' => [
                    Rule::requiredIf($interaction->type === MysteryInteraction::TYPE_FINAL_ANSWER),
                    'nullable',
                    'string',
                    'in:correct,incorrect',
                ],
            ])->validate();

            $interaction->update([
                'admin_response' => $data['admin_response'] ?? null,
                'result' => $data['result'] ?? null,
                'responded_by' => $responder->id,
                'responded_at' => now(),
            ]);

            if ($shouldNotify) {
                $this->pushNotification->sendToUserOrAll($interaction->participant, [
                    'title' => 'Enigma da Akiba',
                    'body' => 'Sua pergunta no Enigma da Akiba recebeu uma resposta.',
                    'url' => '/midias',
                    'icon' => '/favicon.ico',
                ]);

                $interaction->update(['response_notified_at' => now()]);
            }

            return $interaction;
        });
    }

    public function active(): ?Mystery
    {
        return Mystery::query()
            ->active()
            ->with(['author', ...$this->publicRelations()])
            ->latest()
            ->first();
    }

    public function filter(array $filters = []): Collection
    {
        return Mystery::query()
            ->when($filters['with'] ?? null, fn (Builder $query, array $with) => $query->with($with))
            ->latest()
            ->get();
    }

    public function publicRelations(): array
    {
        return [
            'interactions' => fn ($query) => $query->with(['participant', 'responder'])->latest(),
        ];
    }
}
