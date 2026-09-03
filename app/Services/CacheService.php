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
        $domains = $this->domains($domains);
        $cacheKey = $this->key($key, $domains);
        $this->trackKey($cacheKey, $domains);

        return Cache::remember(
            $cacheKey,
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

    public function invalidateUsers(): void
    {
        $this->incrementVersion('users');
    }

    public function invalidateRoles(): void
    {
        $this->incrementVersion('roles');
    }

    public function invalidateActivities(): void
    {
        $this->incrementVersion('activities');
        $this->incrementVersion('calendar');
    }

    public function invalidateCalendar(): void
    {
        $this->incrementVersion('calendar');
    }

    public function invalidateTasks(): void
    {
        $this->incrementVersion('tasks');
    }

    public function invalidateRepositories(): void
    {
        $this->incrementVersion('repositories');
    }

    public function invalidateFormSubmissions(): void
    {
        $this->incrementVersion('form-submissions');
    }

    public function invalidateTrash(): void
    {
        $this->incrementVersion('trash');
    }

    private function key(string|array $key, array $domains): string
    {
        $parts = is_array($key) ? $key : [$key];

        return collect($parts)
            ->map(fn (mixed $part) => is_array($part) ? md5(json_encode($part)) : (string) $part)
            ->prepend($this->versions($domains))
            ->prepend('akiba')
            ->implode(':');
    }

    private function versions(array $domains): string
    {
        return collect($this->domains($domains))
            ->unique()
            ->sort()
            ->map(fn (string $domain) => $domain.'-'.$this->version($domain))
            ->implode('|');
    }

    private function domains(array $domains): array
    {
        return $domains ?: ['posts'];
    }

    private function version(string $domain): int
    {
        return (int) Cache::rememberForever($this->versionKey($domain), fn () => 1);
    }

    private function incrementVersion(string $domain): void
    {
        $this->forgetTrackedKeys($domain);
        Cache::forever($this->versionKey($domain), $this->version($domain) + 1);
    }

    private function versionKey(string $domain): string
    {
        return "akiba:version:{$domain}";
    }

    private function trackKey(string $key, array $domains): void
    {
        foreach ($domains as $domain) {
            $keys = Cache::get($this->registryKey($domain), []);

            if (! in_array($key, $keys, true)) {
                $keys[] = $key;
                Cache::forever($this->registryKey($domain), $keys);
            }
        }
    }

    private function forgetTrackedKeys(string $domain): void
    {
        $registryKey = $this->registryKey($domain);
        $keys = Cache::get($registryKey, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget($registryKey);
    }

    private function registryKey(string $domain): string
    {
        return "akiba:registry:{$domain}";
    }
}
