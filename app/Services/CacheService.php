<?php

namespace App\Services;

use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    private const TTL_MINUTES = 30;

    public function remember(string|array $key, callable $callback, ?int $minutes = null, array $domains = []): mixed
    {
        return Cache::remember(
            $this->key($key, $domains),
            now()->addMinutes($minutes ?? self::TTL_MINUTES),
            $callback
        );
    }

    public function invalidatePosts(?Post $post = null): void
    {
        $this->incrementVersion('posts');

        if ($post?->module === 'review') {
            $this->incrementVersion('reviews');
        }

        if ($post?->module === 'event') {
            $this->incrementVersion('events');
        }
    }

    public function invalidatePodcasts(?Podcast $podcast = null): void
    {
        $this->incrementVersion('podcasts');
    }

    public function invalidatePolls(): void
    {
        $this->incrementVersion('polls');
    }

    public function invalidateMysteries(): void
    {
        $this->incrementVersion('mysteries');
    }

    public function invalidateMedia(): void
    {
        $this->incrementVersion('media');
    }

    private function key(string|array $key, array $domains): string
    {
        $parts = is_array($key) ? $key : [$key];

        return collect($parts)
            ->map(fn (mixed $part) => is_array($part) ? md5(json_encode($part)) : (string) $part)
            ->prepend($this->versions($domains))
            ->prepend('public')
            ->implode(':');
    }

    private function versions(array $domains): string
    {
        return collect($domains ?: ['posts'])
            ->unique()
            ->sort()
            ->map(fn (string $domain) => $domain.'-'.$this->version($domain))
            ->implode('|');
    }

    private function version(string $domain): int
    {
        return (int) Cache::rememberForever($this->versionKey($domain), fn () => 1);
    }

    private function incrementVersion(string $domain): void
    {
        Cache::forever($this->versionKey($domain), $this->version($domain) + 1);
    }

    private function versionKey(string $domain): string
    {
        return "public:version:{$domain}";
    }
}
