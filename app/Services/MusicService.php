<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Music;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Processing\ImageProcess;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MusicService
{
    public function __construct(
        private ImageProcess $image,
    ) {}

    public function refreshMusicRanking(): void
    {
        DB::transaction(function () {
            Music::inRanking()->update([
                'in_ranking' => false
            ]);

            Music::automatic()->orderBy('song_requests_total', 'desc')->limit(10)->update([
                'in_ranking' => true,
            ]);
        });
    }

    public function update(Music $music, array $data, ?UploadedFile $image = null, ?UploadedFile $imageRanking = null): Music
    {
        return DB::transaction(function () use ($music, $data, $image, $imageRanking) {
            $animeName = $data['anime'] ?? $data['production'];
            $animeSlug = Str::slug($animeName) ?: Str::uuid()->toString();
            $currentAnime = $music->anime;

            $anime = $this->storeAnime(
                $animeName,
                $this->image->store(
                    'animes',
                    $image,
                    $currentAnime?->slug === $animeSlug ? $currentAnime->image : null,
                ),
                $currentAnime,
                $animeSlug,
            );

            $music->fill([
                'anime_id' => $anime->id,
                'type' => $data['type'],
                'artist' => $data['artist'],
                'name' => $data['name'],
                'image_ranking' => $this->image->store('musics/ranking', $imageRanking, $music->image_ranking),
            ]);

            if ($music->isDirty()) {
                $music->save();
            }

            return $music;
        });
    }


    private function storeAnime(string $name, ?string $image = null, ?Anime $anime = null, ?string $slug = null): Anime
    {
        $slug ??= Str::slug($name) ?: Str::uuid()->toString();

        if (! $anime || $anime->slug !== $slug) {
            $anime = Anime::firstOrNew(['slug' => $slug]);
        }

        $anime->fill([
            'name' => $name,
            'slug' => $slug,
            'image' => filled($image) ? $image : $anime->image,
        ]);

        if (! $anime->exists || $anime->isDirty()) {
            $anime->save();
        }

        return $anime;
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Music::query()
            ->with('anime')
            ->when(
                $filters['in_ranking'] ?? false,
                fn (Builder $query) => $query->inRanking()
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            )
            ->when(
                $filters['limit'] ?? null,
                fn (Builder $query, int $limit) => $query->limit($limit)
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }
}
