<?php

namespace App\Filters;

use App\Models\SongRequest;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SongRequestFilter
{
    public function apply(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = SongRequest::query()
            ->with('oauthAccount')
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
