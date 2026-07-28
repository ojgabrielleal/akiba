<?php

namespace App\Filters;

use App\Models\Post;
use App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PostFilter
{
    public function apply(array $filters = []): Collection|LengthAwarePaginator
    {
        $user = $filters['user'] ?? null;

        $query = Post::query()
            ->when(
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
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
                $filters['except'] ?? null,
                fn (Builder $query, Post $post) => $query->whereKeyNot($post->getKey())
            )
            ->when(
                $filters['tag'] ?? null,
                fn (Builder $query, string $tag) => $query->whereHas(
                    'tags',
                    fn (Builder $query) => $query->where('name', $tag)
                )
            )
            ->when(
                $filters['tags'] ?? null,
                fn (Builder $query, array $tags) => $query->whereHas(
                    'tags',
                    fn (Builder $query) => $query->whereIn('name', $tags)
                )
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
                $filters['viewed_since'] ?? null,
                fn (Builder $query, $viewedSince) => $query
                    ->whereHas(
                        'views',
                        fn (Builder $viewsQuery) => $viewsQuery->where(
                            'created_at',
                            '>=',
                            $viewedSince
                        )
                    )
                    ->withCount([
                        'views' => fn (Builder $viewsQuery) => $viewsQuery->where(
                            'created_at',
                            '>=',
                            $viewedSince
                        ),
                    ])
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->whereLike(
                    'title',
                    '%'.trim($search).'%'
                )
            )
            ->when(
                ($filters['order_by'] ?? null) === 'random',
                fn (Builder $query) => $query->inRandomOrder(),
                fn (Builder $query) => $query->orderBy(
                    $filters['order_by'] ?? 'id',
                    $filters['order_direction'] ?? 'desc'
                )
            )
            ->when(
                $filters['limit'] ?? null,
                fn (Builder $query, int $limit) => $query->limit($limit)
            )
            ->when(
                $this->shouldRestrictToOwnPosts($user, $filters),
                fn (Builder $query) => $query->where(function (Builder $query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhereHas(
                            'reviews',
                            fn (Builder $query) => $query->where('user_id', $user->id)
                        );
                })
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage)->withQueryString(),
            fn (Builder $query) => $query->get()
        );
    }

    private function shouldRestrictToOwnPosts(?User $user, array $filters): bool
    {
        return $user !== null
            && ! ($filters['ignore_authorization'] ?? false)
            && ! $user->hasPermission('post.list')
            && $user->hasPermission('post.list.own');
    }
}
