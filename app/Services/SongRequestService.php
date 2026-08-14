<?php

namespace App\Services;

use App\Models\Music;
use App\Models\OAuthAccount;
use App\Models\Onair;
use App\Models\SongRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SongRequestService
{
    public function store(array $data, Model $requester): SongRequest
    {
        $songRequest = DB::transaction(function () use ($data, $requester) {
            $onair = $this->storeAcceptingSongRequestsOnair();
            $music = isset($data['music']) ? $this->storeMusic($data['music']) : null;

            if ($requester instanceof OAuthAccount) {
                $this->storeCompleteOAuthAccountProfile($requester, $data);
            }

            $songRequest = $onair->songRequests()->make([
                'type' => $music ? 'music' : 'message',
                'music_id' => $music?->id,
                'message' => $data['message'] ?? null,
            ]);

            $songRequest->requester()->associate($requester);
            $songRequest->save();

            return $songRequest;
        });

        $this->storeNotifyCurrentLocutor($songRequest);

        return $songRequest;
    }

    private function storeAcceptingSongRequestsOnair(): Onair
    {
        return Onair::acceptingSongRequests()
            ->with('program.host')
            ->firstOrFail();
    }

    private function storeMusic(array $data): Music
    {
        $music = Music::where('name', $data['name'])->first();

        if (! $music) {
            return Music::create([
                'production' => $data['production'],
                'type' => $data['type'],
                'artist' => $data['artist'],
                'name' => $data['name'],
                'image' => $data['image'],
            ]);
        }

        $music->increment('song_requests_total');

        return $music;
    }

    private function storeCompleteOAuthAccountProfile(OAuthAccount $oauthAccount, array $data): void
    {
        if (empty($data['address']) || empty($data['birth_date'])) {
            return;
        }

        $oauthAccount->update([
            'address' => $data['address'],
            'birth_date' => $data['birth_date'],
            'profile_completed_at' => now(),
        ]);
    }

    private function storeNotifyCurrentLocutor(SongRequest $songRequest): void
    {
        $songRequest->loadMissing(['music', 'onair.program.host', 'requester']);

        app(PushNotificationService::class)->sendToUserOrAll(
            $songRequest->onair?->program?->host,
            [
                'title' => $songRequest->music ? 'Novo pedido chegou!' : 'Novo recado chegou!',
                'body' => $songRequest->music
                    ? "Ouvinte {$this->storeRequesterName($songRequest)} fez um pedido de música neste momento."
                    : "{$this->storeRequesterName($songRequest)} mandou um recado.",
                'url' => '/panel/locution',
                'icon' => '/img/notifications/songRequestNotification.webp',
            ],
        );
    }

    private function storeRequesterName(SongRequest $songRequest): string
    {
        return $songRequest->requester?->nickname
            ?? $songRequest->requester?->name
            ?? 'Um ouvinte';
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = SongRequest::query()
            ->with('requester')
            ->when(
                $filters['onair_id'] ?? null,
                fn (Builder $query, int $onairId) => $query->where('onair_id', $onairId)
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }
}
