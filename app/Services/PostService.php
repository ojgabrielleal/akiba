<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use App\Models\PostComment;
use Illuminate\Database\Eloquent\Model;
use App\Models\PostReaction;
use App\Models\User;
use App\Processing\ImageProcess;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(
        private ImageProcess $image,
        private PushNotificationService $pushNotification,
    ) {}

    public function deactivate(Post $post): Post
    {
        return DB::transaction(function () use ($post) {
            $post->update(['is_active' => false]);
            return $post;
        });
    }

    public function storeComment(Post $post, Model $author, array $data): PostComment
    {
        return DB::transaction(function () use ($post, $author, $data) {
            $comment = $post->comments()->make([
                'parent_id' => $data['parent_id'] ?? null,
                'comment' => $data['comment'],
            ]);

            $comment->author()->associate($author);
            $comment->save();

            return $comment;
        });
    }

    public function updateComment(PostComment $comment, array $data): PostComment
    {
        return DB::transaction(function () use ($comment, $data) {
            $comment->update([
                'comment' => $data['comment'],
            ]);

            return $comment;
        });
    }

    public function deleteComment(PostComment $comment): void
    {
        DB::transaction(fn () => $comment->delete());
    }

    public function storeReaction(Post $post, Model $reactor, string $name): PostReaction
    {
        return DB::transaction(fn () => $post->reactions()->updateOrCreate(
            [
                'reactor_type' => $reactor->getMorphClass(),
                'reactor_id' => $reactor->getKey(),
            ],
            ['name' => $name],
        ));
    }

    public function store(User $user, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null): Post
    {
        $post = DB::transaction(function () use ($user, $data, $image, $cover) {
            $post = $this->storeStorePost($user, $data, $image, $cover);
            $this->storeStoreTags($post, $data['tags'] ?? []);
            $this->storeStoreReferences($post, $data['references'] ?? []);
            $this->storeStoreReview($post, $user, $data['review'] ?? []);

            return $post;
        });

        if ($post->status === 'published') {
            $this->sendPublishedNotification($post);
        }

        return $post;
    }

    private function storeStorePost(User $user, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null): Post
    {
        $metadata = $this->normalizeMetadata($data);

        return Post::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'status' => $data['status'] ?? 'published',
            'content' => $data['content'] ?? null,
            'image' => $this->image->store('posts', $image),
            'cover' => $this->image->store('posts', $cover),
            'module' => $data['module'] ?? 'post',
            'metadata' => $metadata,
        ]);
    }

    private function storeStoreTags(Post $post, array $tags): void
    {
        if (! empty($tags)) {
            $post->tags()->createMany($tags);
        }
    }

    private function storeStoreReferences(Post $post, array $references): void
    {
        if (! empty($references)) {
            $post->references()->createMany($references);
        }
    }

    private function storeStoreReview(Post $post, User $user, array $review): void
    {
        if (empty($review)) {
            return;
        }

        $post->reviews()->create([
            'user_id' => $user->id,
            'status' => $review['status'],
            'content' => $review['content'],
        ]);
    }

    public function toggleLike(Post $post, ?Model $liker, string $visitorToken): bool
    {
        return DB::transaction(function () use ($post, $liker, $visitorToken) {
            $query = $post->likes();

            if ($liker) {
                $query
                    ->where('liker_type', $liker->getMorphClass())
                    ->where('liker_id', $liker->getKey());
            } else {
                $query->where('visitor_token', $visitorToken);
            }

            $like = $query->first();

            if ($like) {
                $like->delete();

                return false;
            }

            $like = $post->likes()->make([
                'visitor_token' => $liker ? null : $visitorToken,
            ]);

            if ($liker) {
                $like->liker()->associate($liker);
            }

            $like->save();

            return true;
        });
    }

    public function update(Post $post, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null): Post
    {
        $wasPublished = $post->status === 'published';

        $post = DB::transaction(function () use ($post, $data, $image, $cover) {
            $this->updateUpdatePost($post, $data, $image, $cover);
            $this->updateSyncTags($post, $data['tags'] ?? []);
            $this->updateSyncReferences($post, $data['references'] ?? []);
            $this->updateUpdateReview($post, $data['review'] ?? []);

            return $post;
        });

        if (! $wasPublished && $post->status === 'published') {
            $this->sendPublishedNotification($post);
        }

        return $post;
    }

    private function sendPublishedNotification(Post $post): void
    {
        $this->pushNotification->sendToUserOrAll(null, [
            'title' => $this->publishedNotificationTitle($post),
            'body' => $post->title,
            'url' => $this->publishedNotificationUrl($post),
            'icon' => '/favicon.ico',
            'banner' => $post->cover,
        ]);
    }

    private function publishedNotificationTitle(Post $post): string
    {
        return match ($post->module) {
            'review' => 'Review nova na Akiba',
            'event' => 'Evento novo na Akiba',
            default => 'Matéria nova na Akiba',
        };
    }

    private function publishedNotificationUrl(Post $post): string
    {
        return match ($post->module) {
            'review' => route('review.read', $post->slug),
            'event' => route('event.read', $post->slug),
            default => route('post.read', $post->slug),
        };
    }

    private function updateUpdatePost(Post $post, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null): void
    {
        $module = $data['module'] ?? $post->module;
        $metadata = $this->normalizeMetadata($data, $module);

        $post->fill([
            'title' => $data['title'],
            'status' => $data['status'] ?? 'published',
            'content' => $data['content'] ?? null,
            'image' => $this->image->store('posts', $image, $post->image),
            'cover' => $this->image->store('posts', $cover, $post->cover),
            'module' => $module,
            'metadata' => $metadata,
        ]);

        if ($post->isDirty()) {
            $post->save();
        }
    }

    private function updateSyncTags(Post $post, array $tags): void
    {
        foreach ($tags as $tag) {
            $post->tags()->updateOrCreate(
                ['uuid' => $tag['uuid'] ?? null],
                ['name' => $tag['name']]
            );
        }
    }

    private function updateSyncReferences(Post $post, array $references): void
    {
        foreach ($references as $reference) {
            $post->references()->updateOrCreate(
                ['uuid' => $reference['uuid'] ?? null],
                ['name' => $reference['name'], 'url' => $reference['url']]
            );
        }
    }

    private function updateUpdateReview(Post $post, array $review): void
    {
        if (empty($review)) {
            return;
        }

        $post->reviews()->where('uuid', $review['uuid'])->update([
            'status' => $review['status'],
            'content' => $review['content'],
        ]);
    }

    private function normalizeMetadata(array $data, ?string $module = null): ?array
    {
        $metadata = $data['metadata'] ?? null;

        if (! is_array($metadata)) {
            return null;
        }

        if (($module ?? $data['module'] ?? 'post') === 'review' && isset($metadata['date_of_release'])) {
            $metadata['date_of_release'] = Carbon::parse($metadata['date_of_release'])->toDateString();
            unset($metadata['year_of_release']);
        }

        return $metadata;
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
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
                        ->when(
                            $tokens->count() > 1,
                            fn (Builder $query) => $query->orWhereLike('content', $term)
                        )
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
                fn (Builder $query) => $this->filterApplyOrdering($query, $filters)
            )
            ->when(
                $filters['limit'] ?? null,
                fn (Builder $query, int $limit) => $query->limit($limit)
            )
            ->when(
                $this->filterShouldRestrictToOwnPosts($user, $filters),
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

    private function filterShouldRestrictToOwnPosts(?User $user, array $filters): bool
    {
        return $user !== null
            && ! ($filters['ignore_authorization'] ?? false)
            && ! $user->hasPermission('post.list')
            && $user->hasPermission('post.list.own');
    }

    private function filterApplyOrdering(Builder $query, array $filters): Builder
    {
        $orderDirection = $filters['order_direction'] ?? 'desc';

        return match ($filters['order_by'] ?? 'id') {
            'interactions_count' => $query->orderByRaw(
                "(views_count + likes_count + comments_count) {$orderDirection}"
            ),
            'metadata_date_of_release' => $query->orderByRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.date_of_release')) {$orderDirection}"
            ),
            'metadata_event_date' => $query->orderByRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.event_date')) {$orderDirection}"
            ),
            default => $query->orderBy(
                $filters['order_by'] ?? 'id',
                $orderDirection
            ),
        };
    }}
