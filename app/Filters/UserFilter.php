<?php

namespace App\Filters;

use App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserFilter
{
    public function apply(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = User::query()
            ->when(
                $filters['active'] ?? false,
                fn (Builder $query) => $query->active()
            )
            ->when(
                array_key_exists('is_virtual', $filters),
                fn (Builder $query) => $query->where('is_virtual', $filters['is_virtual'])
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->whereLike('name', '%'.trim($search).'%')
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
