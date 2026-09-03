<?php

namespace Tests\Unit\Services;

use App\Services\CacheService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheTest extends TestCase
{
    public function test_it_reuses_cached_values_until_their_domain_is_invalidated(): void
    {
        $cache = app(CacheService::class);
        $postKey = ['test', 'post', Str::uuid()->toString()];
        $pollKey = ['test', 'poll', Str::uuid()->toString()];
        $postBuilds = 0;
        $pollBuilds = 0;

        $this->assertSame(1, $cache->remember($postKey, function () use (&$postBuilds) {
            return ++$postBuilds;
        }, domains: ['posts']));

        $this->assertNotEmpty(Cache::get('akiba:registry:posts'));

        $this->assertSame(1, $cache->remember($postKey, function () use (&$postBuilds) {
            return ++$postBuilds;
        }, domains: ['posts']));

        $this->assertSame(1, $cache->remember($pollKey, function () use (&$pollBuilds) {
            return ++$pollBuilds;
        }, domains: ['polls']));

        $cache->invalidatePosts();

        $this->assertNull(Cache::get('akiba:registry:posts'));

        $this->assertSame(1, $cache->remember($pollKey, function () use (&$pollBuilds) {
            return ++$pollBuilds;
        }, domains: ['polls']));

        $this->assertSame(2, $cache->remember($postKey, function () use (&$postBuilds) {
            return ++$postBuilds;
        }, domains: ['posts']));
    }
}
