<?php

namespace App\Filters;

use App\Models\Music;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MusicFilter
{
    public function apply(array $filters = []): Collection|LengthAwarePaginator
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
    }
}
