<?php

namespace App\Services;

use App\Models\EnigmaGame;
use App\Models\EnigmaGameInteraction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EnigmaGameService
{
    public function __construct(
        private PushNotificationService $pushNotification,
        private CacheService $cache,
    ) {}

    public function store(User $user, array $data): EnigmaGame
    {
        $enigmagame = DB::transaction(function () use ($user, $data): EnigmaGame {
            $enigmagame = EnigmaGame::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $data['status'] ?? EnigmaGame::STATUS_DRAFT,
                'solution' => $data['solution'] ?? null,
            ]);

            if ($enigmagame->status === EnigmaGame::STATUS_ACTIVE) {
                $this->publish($enigmagame);
            }

            return $enigmagame;
        });

        $this->cache->invalidateMysteries();

        return $enigmagame;
    }

    public function update(EnigmaGame $enigmagame, array $data): EnigmaGame
    {
        $enigmagame = DB::transaction(function () use ($enigmagame, $data): EnigmaGame {
            $enigmagame->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $data['status'] ?? $enigmagame->status,
                'solution' => $data['solution'] ?? null,
            ]);

            if ($enigmagame->status === EnigmaGame::STATUS_ACTIVE) {
                $this->publish($enigmagame);
            }

            return $enigmagame;
        });

        $this->cache->invalidateMysteries();

        return $enigmagame;
    }

    public function publish(EnigmaGame $enigmagame): EnigmaGame
    {
        $enigmagame = DB::transaction(function () use ($enigmagame): EnigmaGame {
            EnigmaGame::query()->lockForUpdate()->whereKeyNot($enigmagame->id)->active()->update([
                'status' => EnigmaGame::STATUS_ENDED,
            ]);

            $enigmagame->update(['status' => EnigmaGame::STATUS_ACTIVE]);

            return $enigmagame;
        });

        $this->cache->invalidateMysteries();

        return $enigmagame;
    }

    public function delete(EnigmaGame $enigmagame): void
    {
        DB::transaction(fn () => $enigmagame->delete());

        $this->cache->invalidateMysteries();
    }

    public function deactivate(EnigmaGame $enigmagame): EnigmaGame
    {
        $enigmagame = DB::transaction(function () use ($enigmagame): EnigmaGame {
            $enigmagame->update(['status' => EnigmaGame::STATUS_INACTIVE]);

            return $enigmagame;
        });

        $this->cache->invalidateMysteries();

        return $enigmagame;
    }

    public function finish(EnigmaGame $enigmagame): EnigmaGame
    {
        $enigmagame = DB::transaction(function () use ($enigmagame): EnigmaGame {
            $enigmagame->update(['status' => EnigmaGame::STATUS_ENDED]);

            return $enigmagame;
        });

        $this->cache->invalidateMysteries();

        return $enigmagame;
    }

    public function interact(EnigmaGame $enigmagame, Model $participant, array $data): EnigmaGameInteraction
    {
        $interaction = DB::transaction(function () use ($enigmagame, $participant, $data): EnigmaGameInteraction {
            $enigmagame = EnigmaGame::query()->lockForUpdate()->active()->whereKey($enigmagame->id)->firstOrFail();

            if ($enigmagame->interactions()
                ->where('type', EnigmaGameInteraction::TYPE_FINAL_ANSWER)
                ->where('result', 'correct')
                ->exists()) {
                throw ValidationException::withMessages([
                    'content' => 'Este enigma ja foi resolvido.',
                ]);
            }

            $query = $enigmagame->interactions()
                ->lockForUpdate()
                ->where('participant_type', $participant->getMorphClass())
                ->where('participant_id', $participant->getKey());

            if ((clone $query)->where('type', EnigmaGameInteraction::TYPE_FINAL_ANSWER)->exists()) {
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

            return $enigmagame->interactions()->create([
                'participant_type' => $participant->getMorphClass(),
                'participant_id' => $participant->getKey(),
                'type' => $data['type'],
                'content' => $data['content'],
            ]);
        });

        $this->cache->invalidateMysteries();

        return $interaction;
    }

    public function respond(EnigmaGameInteraction $interaction, User $responder, array $data): EnigmaGameInteraction
    {
        $interaction = DB::transaction(function () use ($interaction, $responder, $data): EnigmaGameInteraction {
            $interaction = EnigmaGameInteraction::query()
                ->with('participant')
                ->lockForUpdate()
                ->whereKey($interaction->id)
                ->firstOrFail();

            $shouldNotify = $interaction->type === EnigmaGameInteraction::TYPE_QUESTION
                && blank($interaction->admin_response)
                && blank($interaction->response_notified_at);

            validator($data, [
                'admin_response' => [
                    Rule::requiredIf($interaction->type === EnigmaGameInteraction::TYPE_QUESTION),
                    'nullable',
                    'string',
                ],
                'result' => [
                    Rule::requiredIf($interaction->type === EnigmaGameInteraction::TYPE_FINAL_ANSWER),
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
                    'title' => 'Enigma Game',
                    'body' => 'Sua pergunta no Enigma Game recebeu uma resposta.',
                    'url' => '/midias',
                    'icon' => '/favicon.ico',
                ]);

                $interaction->update(['response_notified_at' => now()]);
            }

            return $interaction;
        });

        $this->cache->invalidateMysteries();

        return $interaction;
    }

    public function active(): ?EnigmaGame
    {
        return EnigmaGame::query()
            ->active()
            ->with(['author', ...$this->publicRelations()])
            ->latest()
            ->first();
    }

    public function filter(array $filters = []): Collection
    {
        return EnigmaGame::query()
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
