<?php

namespace App\Filters;

use App\Models\Post;
use App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PostFilter
{
    public function apply(User $user, array $filters = []): Collection|LengthAwarePaginator
    {

        $query = Post::query()
            ->when(
                $filters['active'] ?? false,
                fn (Builder $query) => $query->active()
            )
            ->when(
                $filters['status'] ?? null,
                fn (Builder $query, string $status) => $query->withStatus($status)
            )
            ->when(
                $filters['authored_by'] ?? null,
                fn (Builder $query, User $author) => $query->authoredBy($author)
            )
            ->when(
                $filters['module'] ?? null,
                fn (Builder $query, string $module) => $query->forModule($module)
            )
            ->when(
                $filters['with_count'] ?? null,
                fn (Builder $query, array|string $relations) => $query->withCount($relations)
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->whereLike(
                    'title',
                    '%'.trim($search).'%'
                )
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            )
            ->when(
                $filters['limit'] ?? null,
                fn (Builder $query, int $limit) => $query->limit($limit)
            );

        if (
            ! ($filters['ignore_authorization'] ?? false)
            && ! $user->hasPermission('post.list')
            && $user->hasPermission('post.list.own')
        ) {
            $query->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas(
                        'reviews',
                        fn (Builder $query) => $query->where('user_id', $user->id)
                    );
            });
        }

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }
}
