<?php

namespace App\Services;

use App\Models\Podcast;
use App\Models\PodcastListen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PodcastListenService
{
    public function __construct(
        private BadgeService $badges,
        private CacheService $cache,
    ) {}

    public function markListened(Podcast $podcast, Model $listener): PodcastListen
    {
        $listen = DB::transaction(function () use ($podcast, $listener): PodcastListen {
            return PodcastListen::query()->firstOrCreate([
                'podcast_id' => $podcast->id,
                'listener_type' => $listener->getMorphClass(),
                'listener_id' => $listener->getKey(),
            ], [
                'listened_at' => now(),
            ]);
        });

        $this->badges->awardPodcastListenAchievements($listener);
        $this->cache->invalidatePodcasts($podcast);

        return $listen;
    }
}
