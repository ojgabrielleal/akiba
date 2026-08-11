<?php

namespace App\Services;

use App\Models\Music;
use Illuminate\Support\Facades\DB;
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

            Music::orderBy('song_requests_total', 'desc')->limit(10)->update([
                'in_ranking' => true,
            ]);
        });
    }

    public function update(Music $music, array $data, ?UploadedFile $image = null, ?UploadedFile $imageRanking = null): Music
    {
        return DB::transaction(function () use ($music, $data, $image, $imageRanking) {
            $music->fill([
                'type' => $data['type'],
                'production' => $data['production'],
                'artist' => $data['artist'],
                'name' => $data['name'],
                'image' => $this->image->store('musics', $image, $music->image),
                'image_ranking' => $this->image->store('musics/ranking', $imageRanking, $music->image_ranking),
            ]);

            if ($music->isDirty()) {
                $music->save();
            }

            return $music;
        });
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Music::query()
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
    }}
