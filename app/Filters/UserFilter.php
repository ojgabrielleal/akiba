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
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
            )
            ->when(
                array_key_exists('is_virtual', $filters),
                fn (Builder $query) => $query->where('is_virtual', $filters['is_virtual'])
            )
            ->when(
                $filters['has_roles'] ?? false,
                fn (Builder $query) => $query->whereHas('roles')
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                    $term = '%'.trim($search).'%';

                    $query->whereLike('name', $term)
                        ->orWhereLike('nickname', $term);
                })
            )
            ->when(
                $filters['virtual_last'] ?? false,
                fn (Builder $query) => $query->orderBy('is_virtual', 'asc')
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
