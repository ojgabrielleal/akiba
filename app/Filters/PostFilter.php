<?php

namespace App\Filters;

use App\Models\Post;
use App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

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
                $filters['event_date_from'] ?? null,
                fn (Builder $query, $date) => $query->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.event_date')) >= ?",
                    [$date]
                )
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
                $filters['interacted_since'] ?? null,
                fn (Builder $query, $interactedSince) => $query
                    ->where(function (Builder $query) use ($interactedSince) {
                        $query->whereHas(
                            'views',
                            fn (Builder $viewsQuery) => $viewsQuery->where(
                                'created_at',
                                '>=',
                                $interactedSince
                            )
                        )
                            ->orWhereHas(
                                'likes',
                                fn (Builder $likesQuery) => $likesQuery->where(
                                    'created_at',
                                    '>=',
                                    $interactedSince
                                )
                            )
                            ->orWhereHas(
                                'comments',
                                fn (Builder $commentsQuery) => $commentsQuery->where(
                                    'created_at',
                                    '>=',
                                    $interactedSince
                                )
                            );
                    })
                    ->withCount([
                        'views as views_count' => fn (Builder $viewsQuery) => $viewsQuery->where(
                            'created_at',
                            '>=',
                            $interactedSince
                        ),
                        'likes as likes_count' => fn (Builder $likesQuery) => $likesQuery->where(
                            'created_at',
                            '>=',
                            $interactedSince
                        ),
                        'comments as comments_count' => fn (Builder $commentsQuery) => $commentsQuery->where(
                            'created_at',
                            '>=',
                            $interactedSince
                        ),
                    ])
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                    $normalizedSearch = trim($search);
                    $term = "%{$normalizedSearch}%";
                    $slugTerm = '%'.Str::slug($normalizedSearch).'%';
                    $tokens = Str::of($normalizedSearch)
                        ->lower()
                        ->replaceMatches('/[^\pL\pN]+/u', ' ')
                        ->explode(' ')
                        ->map(fn (string $token) => trim($token))
                        ->filter(fn (string $token) => Str::length($token) >= 3)
                        ->take(6)
                        ->values();

                    $query->whereLike('title', $term)
                        ->orWhereLike('slug', $slugTerm)
                        ->orWhereLike('content', $term)
                        ->orWhere(function (Builder $query) use ($tokens) {
                            $tokens->each(fn (string $token) => $query->where(function (Builder $query) use ($token) {
                                $tokenTerm = "%{$token}%";

                                $query->whereLike('title', $tokenTerm)
                                    ->orWhereLike('slug', $tokenTerm);
                            }));
                        });
                })
            )
            ->when(
                ($filters['order_by'] ?? null) === 'random',
                fn (Builder $query) => $query->inRandomOrder(),
                fn (Builder $query) => $this->applyOrdering($query, $filters)
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

    private function applyOrdering(Builder $query, array $filters): Builder
    {
        $orderDirection = $filters['order_direction'] ?? 'desc';

        return match ($filters['order_by'] ?? 'id') {
            'interactions_count' => $query->orderByRaw(
                "(views_count + likes_count + comments_count) {$orderDirection}"
            ),
            'metadata_year_of_release' => $query->orderByRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.year_of_release')) AS UNSIGNED) {$orderDirection}"
            ),
            'metadata_event_date' => $query->orderByRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.event_date')) {$orderDirection}"
            ),
            default => $query->orderBy(
                $filters['order_by'] ?? 'id',
                $orderDirection
            ),
        };
    }
}
