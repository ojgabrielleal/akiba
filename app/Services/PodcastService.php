<?php

namespace App\Services;

use App\Models\Podcast;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Processing\ImageProcess;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PodcastService
{
    public function __construct(
        private ImageProcess $image,
    ) {}

    public function deactivate(Podcast $podcast): Podcast
    {
        return DB::transaction(function () use ($podcast) {
            $podcast->update(['is_active' => false]);

            return $podcast;
        });
    }

    public function store(User $user, array $data,): Podcast
    {
        return DB::transaction(fn () => Podcast::create([
            'user_id' => $user->id,
            'image' => $this->image->store('podcasts', $data['image']),
            'season' => $data['season'],
            'episode' => $data['episode'],
            'title' => $data['title'],
            'summary' => $data['summary'],
            'description' => $data['description'],
            'audio' => $data['audio'],
        ]));
    }

    public function update(Podcast $podcast, array $data, ?UploadedFile $image = null): Podcast
    {
        return DB::transaction(function () use ($podcast, $data, $image) {
            $podcast->fill([
                'image' => $this->image->store('podcasts', $image, $podcast->image),
                'season' => $data['season'],
                'episode' => $data['episode'],
                'title' => $data['title'],
                'summary' => $data['summary'],
                'description' => $data['description'],
                'audio' => $data['audio'],
            ]);

            if ($podcast->isDirty()) {
                $podcast->save();
            }

            return $podcast;
        });
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Podcast::query()
            ->when(
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->when(
                $filters['with_count'] ?? null,
                fn (Builder $query, array|string $relations) => $query->withCount($relations)
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                    $term = '%'.trim($search).'%';

                    $query->whereLike('title', $term)
                        ->orWhereLike('summary', $term);
                })
            )
            ->when(
                $filters['except'] ?? null,
                fn (Builder $query, Podcast $podcast) => $query->whereKeyNot($podcast->getKey())
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
