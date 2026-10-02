<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Music;
use App\Models\OAuthAccount;
use App\Models\Onair;
use App\Models\SongRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SongRequestService
{
    public function __construct(
        private BadgeService $badges,
    ) {}

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

        $this->transferMostRequestsBadge();
        $this->storeNotifyCurrentLocutor($songRequest);

        return $songRequest;
    }

    private function transferMostRequestsBadge(): void
    {
        $leader = $this->uniqueMostRequestsLeader();

        if (! $leader) {
            return;
        }

        $this->badges->transferStealableByTrigger(
            'song_request.most_requests',
            $leader,
            'Pessoa que mais fez pedidos musicais.',
            ['trigger' => 'song_request.most_requests'],
        );
    }

    private function uniqueMostRequestsLeader(): ?Model
    {
        $leaders = SongRequest::query()
            ->select('requester_type', 'requester_id')
            ->selectRaw('count(*) as requests_total')
            ->whereNotNull('requester_type')
            ->whereNotNull('requester_id')
            ->groupBy('requester_type', 'requester_id')
            ->orderByDesc('requests_total')
            ->limit(2)
            ->get();

        if ($leaders->isEmpty()) {
            return null;
        }

        if ($leaders->count() > 1 && (int) $leaders[0]->requests_total === (int) $leaders[1]->requests_total) {
            return null;
        }

        $model = $leaders[0]->requester_type;

        if (! is_a($model, Model::class, true)) {
            return null;
        }

        return $model::query()->find($leaders[0]->requester_id);
    }

    private function storeAcceptingSongRequestsOnair(): Onair
    {
        return Onair::acceptingSongRequests()
            ->with('program.host')
            ->firstOrFail();
    }

    private function storeMusic(array $data): Music
    {
        $animeSlug = Str::slug($data['production']) ?: Str::uuid()->toString();
        $isManual = (bool) ($data['is_manual'] ?? false);

        $anime = Anime::firstOrCreate(
            ['slug' => $animeSlug],
            [
                'name' => $data['production'],
                'image' => $data['image'] ?? null,
            ],
        );

        if (! $anime->image && ! empty($data['image'])) {
            $anime->update(['image' => $data['image']]);
        }

        $music = Music::where('anime_id', $anime->id)
            ->where('name', $data['name'])
            ->first();

        if (! $music) {
            return Music::create([
                'anime_id' => $anime->id,
                'type' => $data['type'] ?? 'OVA',
                'artist' => $data['artist'] ?? 'Não informado',
                'name' => $data['name'],
                'is_manual' => $isManual,
                'song_requests_total' => $isManual ? 0 : 1,
            ]);
        }

        if (! $music->is_manual) {
            $music->increment('song_requests_total');
        }

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
        $songRequest->loadMissing(['music.anime', 'onair.program.host', 'requester']);

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
            ->with(['requester', 'music.anime'])
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
