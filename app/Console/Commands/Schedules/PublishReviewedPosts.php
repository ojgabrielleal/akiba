<?php

namespace App\Console\Commands\Schedules;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Console\Command;

class PublishReviewedPosts extends Command
{
    protected $signature = 'posts:publish-reviewed {--hours=24 : Minimum hours a post must stay in review before publication} {--news-hours=2 : Minimum hours a news post must stay in review before publication} {--limit=50 : Maximum posts to publish per run}';

    protected $description = 'Publish active posts that stayed in review for the configured amount of time.';

    public function handle(PostService $posts): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $newsHours = max(1, (int) $this->option('news-hours'));
        $limit = max(1, (int) $this->option('limit'));
        $threshold = now()->subHours($hours);
        $newsThreshold = now()->subHours($newsHours);
        $published = 0;

        Post::query()
            ->active()
            ->withStatus('revision')
            ->where(function ($query) use ($threshold, $newsThreshold) {
                $query
                    ->where('updated_at', '<=', $threshold)
                    ->orWhere(function ($query) use ($newsThreshold) {
                        $query
                            ->where('updated_at', '<=', $newsThreshold)
                            ->whereHas('tags', fn ($query) => $query->where('name', 'news'));
                    });
            })
            ->withExists([
                'tags as is_news' => fn ($query) => $query->where('name', 'news'),
            ])
            ->orderByDesc('is_news')
            ->oldest('updated_at')
            ->limit($limit)
            ->get()
            ->each(function (Post $post) use ($posts, &$published) {
                $posts->publish($post);
                $published++;

                $this->line("Published post {$post->uuid} ({$post->title}).");
            });

        $this->info("Published {$published} reviewed post(s). Waiting time: {$hours} hour(s). News waiting time: {$newsHours} hour(s).");

        return self::SUCCESS;
    }
}
