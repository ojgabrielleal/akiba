<?php

namespace App\Services;

use App\Models\Onair;
use App\Models\Program;
use App\Models\SongRequest;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Integrations\DiscordWebhookService;

class LocutionService
{
    public function __construct(
        private DiscordWebhookService $discord,
    ) {}

    public function finish(): void
    {
        DB::transaction(function () {
            $onair = $this->finishLiveOnair();
            $auto = $this->finishDefaultAutoDj();

            if ($onair) {
                $this->finishFinishOnair($onair);
            }

            if ($auto) {
                $this->finishStartAutoDj($auto);
            }
        });
    }

    private function finishLiveOnair(): ?Onair
    {
        return Onair::live()->first();
    }

    private function finishDefaultAutoDj(): ?Program
    {
        return Program::where('execution_mode', 'auto_dj')
            ->where('is_default_auto_dj', true)
            ->first();
    }

    private function finishFinishOnair(Onair $onair): void
    {
        $onair->update([
            'in_air' => false,
            'allows_song_requests' => false,
        ]);

        SongRequest::where('onair_id', $onair->id)
            ->where('was_reproduced', false)
            ->where('was_canceled', false)
            ->where('type', 'music')
            ->update(['was_canceled' => true]);

        SongRequest::where('onair_id', $onair->id)
            ->where('was_read', false)
            ->where('was_dismissed', false)
            ->where('type', 'message')
            ->update(['was_dismissed' => true]);
    }

    private function finishStartAutoDj(Program $auto): void
    {
        $auto->onair()->create([
            'execution_mode' => 'auto_dj',
            'phrase' => $this->finishRandomPhrase($auto),
        ]);
    }

    private function finishRandomPhrase(Program $auto): array
    {
        $selected = collect($auto->phrases)->random();

        return [
            'text' => $selected['text'],
            'icon' => $selected['icon'],
            'decoration' => $selected['decoration'],
        ];
    }

    public function markSongRequestAsCanceled(SongRequest $songRequest): SongRequest
    {
        return DB::transaction(function () use ($songRequest) {
            if ($songRequest->type === 'message') {
                $songRequest->update(['was_dismissed' => true]);
            } else {
                $songRequest->update(['was_canceled' => true]);
                $songRequest->onair()->decrement('song_requests_total');
            }

            return $songRequest;
        });
    }

    public function markSongRequestAsPlayed(SongRequest $songRequest): SongRequest
    {
        return DB::transaction(function () use ($songRequest) {
            if ($songRequest->type === 'message') {
                $songRequest->update(['was_read' => true]);
            } else {
                $songRequest->update(['was_reproduced' => true]);
                $songRequest->onair()->increment('song_requests_total');
            }

            return $songRequest;
        });
    }

    public function start(User $user, Program $program, array $data): void
    {
        DB::transaction(function () use ($user, $program, $data) {
            $this->startFinishLiveOnairs();
            $this->startAssignFreeProgram($program, $user);
            $this->startStartProgram($program, $data);
        });

        if (filter_var($data['send_notification'], FILTER_VALIDATE_BOOLEAN)) {
            $this->startSendNotifications($user, $program);
        }
    }

    private function startFinishLiveOnairs(): void
    {
        Onair::live()->update([
            'in_air' => false,
        ]);
    }

    private function startAssignFreeProgram(Program $program, User $user): void
    {
        if ($program->access_type !== 'free') {
            return;
        }

        $program->update([
            'user_id' => $user->id,
        ]);
    }

    private function startStartProgram(Program $program, array $data): void
    {
        $program->onair()->create([
            'execution_mode' => 'live',
            'phrase' => [
                'text' => $data['phrase']['text'],
                'icon' => $data['phrase']['icon'],
                'decoration' => $data['phrase']['decoration'],
                'texture' => $data['phrase']['texture'],
            ],
            'allows_song_requests' => true,
        ]);
    }

    private function startSendNotifications(User $user, Program $program): void
    {
        $this->discord->sendStreamNotificationHook($user, $program);

        app(PushNotificationService::class)->sendToUserOrAll(null, [
            'title' => "{$program->name} entrou no ar",
            'body' => "{$user->name} está ao vivo na Akiba. Vem sintonizar com a gente.",
            'url' => url('/site'),
            'icon' => url('/favicon.ico'),
        ]);
    }

    public function toggleSongRequestBoxStatus(): ?Onair
    {
        return DB::transaction(function () {
            $onair = Onair::live()->first();

            if (!$onair) {
                return null;
            }

            $onair->update([
                'allows_song_requests' => !$onair->allows_song_requests,
            ]);

            return $onair;
        });
    }
}
