<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SocialPublicationSchedule;
use Illuminate\Support\Collection;

class InstagramStoryService
{
    public function promotedPosts(int $limit = 20): Collection
    {
        return SocialPublicationSchedule::query()
            ->where('publishable_type', (new Post)->getMorphClass())
            ->where('channel', 'instagram')
            ->where('placement', 'story')
            ->where('status', SocialPublicationSchedule::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->whereHasMorph('publishable', [Post::class], fn ($query) => $query
                ->active()
                ->withStatus('published'))
            ->with(['publishable.tags'])
            ->orderByDesc('published_at')
            ->limit(max($limit * 5, $limit))
            ->get()
            ->unique('publishable_id')
            ->take($limit)
            ->values()
            ->map(fn (SocialPublicationSchedule $schedule): array => $this->mapSchedule($schedule));
    }

    public function isPromoted(Post $post): bool
    {
        return $post->is_active
            && $post->status === 'published'
            && $post->socialPublicationSchedules()
                ->where('channel', 'instagram')
                ->where('placement', 'story')
                ->where('status', SocialPublicationSchedule::STATUS_PUBLISHED)
                ->exists();
    }

    public function destination(Post $post): string
    {
        $route = match ($post->module) {
            'review' => 'review.read',
            'event' => 'event.read',
            default => 'post.read',
        };

        return route($route, [
            'slug' => $post->slug,
            'utm_source' => 'instagram',
            'utm_medium' => 'story',
            'utm_campaign' => 'materias',
        ]);
    }

    private function mapSchedule(SocialPublicationSchedule $schedule): array
    {
        /** @var Post $post */
        $post = $schedule->publishable;

        return [
            'uuid' => $post->uuid,
            'title' => $post->title,
            'image' => $post->cover ?: $post->image ?: asset('img/placeholders/placeholder.webp'),
            'category' => $post->tags->first()?->name ?: match ($post->module) {
                'review' => 'Review',
                'event' => 'Evento',
                default => 'Notícia',
            },
            'published_at' => $post->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y'),
            'promoted_at' => $schedule->published_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            'href' => route('stories.post.redirect', [
                'post' => $post->uuid,
                'utm_source' => 'instagram',
                'utm_medium' => 'story',
                'utm_campaign' => 'materias',
            ]),
        ];
    }
}
