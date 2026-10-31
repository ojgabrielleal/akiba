<?php

namespace App\Services;

use App\Integrations\DiscordWebhookService;
use App\Models\Onair;
use App\Models\Program;
use App\Models\SongRequest;
use App\Models\User;
use App\Processing\SocialArtworkProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class LocutionService
{
    public function __construct(
        private DiscordWebhookService $discord,
        private BadgeService $badges,
        private SocialPublicationScheduleService $socialPublication,
        private SocialArtworkDataService $socialArtworkData,
        private SocialArtworkProcess $socialArtwork,
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
            'in_air' => true,
            'user_id' => $auto->user_id,
            'execution_mode' => 'auto_dj',
            'phrase' => $this->finishRandomPhrase($auto),
            'allows_song_requests' => false,
        ]);
    }

    private function finishRandomPhrase(Program $auto): array
    {
        $phrases = collect($auto->phrases)->filter();
        $selected = $phrases->isNotEmpty() ? $phrases->random() : [];

        return [
            'text' => $selected['text'] ?? 'Rede Akiba no automático',
            'icon' => $selected['icon'] ?? '/img/locution/characters/tanjiro.webp',
            'decoration' => $selected['decoration'] ?? [
                'left' => '/img/player/decorations/sound_left_ornaments.svg',
                'right' => '/img/player/decorations/sound_right_ornaments.svg',
            ],
            'texture' => $selected['texture'] ?? '/img/textures/sound_bars_texture.svg',
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
        $songRequest = DB::transaction(function () use ($songRequest) {
            if ($songRequest->type === 'message') {
                $songRequest->update(['was_read' => true]);
            } elseif (! $songRequest->was_reproduced) {
                $songRequest->update(['was_reproduced' => true]);
                $songRequest->onair()->increment('song_requests_total');
            }

            return $songRequest->refresh()->load('requester');
        });

        if ($songRequest->type === 'music' && $songRequest->was_reproduced && $songRequest->requester) {
            $this->badges->awardSongRequestPlayedAchievements($songRequest->requester);
            $this->badges->awardSongRequestOrdinalAchievements($songRequest);
            $this->badges->transferCompetitiveLeaderByTrigger(
                'song_request.most_requests',
                'Pessoa que mais fez pedidos musicais.',
            );
        }

        return $songRequest;
    }

    public function start(User $user, Program $program, array $data): void
    {
        $onair = DB::transaction(function () use ($user, $program, $data) {
            $this->startFinishLiveOnairs();

            return $this->startStartProgram($program, $user, $data);
        });

        $this->startSendNotifications($user, $program, $onair);

        if (isset($data['social'])) {
            $this->socialPublication->scheduleLive($onair, $data['social']);
        }
    }

    private function startFinishLiveOnairs(): void
    {
        Onair::live()->update([
            'in_air' => false,
        ]);
    }

    private function startStartProgram(Program $program, User $user, array $data): Onair
    {
        return $program->onair()->create([
            'user_id' => $user->id,
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

    private function startSendNotifications(User $user, Program $program, Onair $onair): void
    {
        $artworkPath = null;

        if ($this->discord->streamNotificationsEnabled()) {
            try {
                $artworkPath = $this->socialArtwork->generate(
                    'discord-live-feed',
                    $this->socialArtworkData->mapLive($onair),
                    'live-'.$onair->uuid,
                    'discord-'.Str::uuid(),
                )->path;
            } catch (Throwable $exception) {
                Log::warning('Discord live artwork generation failed; the text notification will be used.', [
                    'onair_uuid' => $onair->uuid,
                    'exception' => $exception::class,
                ]);
            }
        }

        try {
            $this->discord->sendStreamNotificationHook($user, $program, $artworkPath);
        } finally {
            if ($artworkPath !== null) {
                Storage::disk('public')->delete($artworkPath);
            }
        }

        app(PushNotificationService::class)->sendToUserOrAll(null, [
            'title' => "{$program->name} entrou no ar",
            'body' => "{$user->name} está ao vivo na Akiba. Vem sintonizar com a gente.",
            'url' => url('/'),
            'icon' => url('/favicon.ico'),
        ]);
    }

    public function toggleSongRequestBoxStatus(): ?Onair
    {
        return DB::transaction(function () {
            $onair = Onair::live()->latest('id')->lockForUpdate()->first();

            if (! $onair) {
                return null;
            }

            $onair->update([
                'allows_song_requests' => ! $onair->allows_song_requests,
            ]);

            return $onair;
        });
    }
}
