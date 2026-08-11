<?php

namespace Tests\Unit\Filters;

use App\Services\PostService;
use App\Models\PageView;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_published_active_posts_ordered_by_views_from_the_last_week(): void
    {
        $user = User::factory()->create();

        $mostViewed = Post::factory()->create([
            'status' => 'published',
        ]);
        $secondMostViewed = Post::factory()->create([
            'status' => 'published',
        ]);
        $viewedOnlyBeforeThePeriod = Post::factory()->create([
            'status' => 'published',
        ]);
        $inactive = Post::factory()->create([
            'is_active' => false,
            'status' => 'published',
        ]);
        $draft = Post::factory()->create([
            'status' => 'draft',
        ]);

        PageView::factory(4)->for($mostViewed, 'viewable')->create();
        PageView::factory(2)->for($secondMostViewed, 'viewable')->create();
        PageView::factory(10)->for($viewedOnlyBeforeThePeriod, 'viewable')->create([
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);
        PageView::factory(5)->for($inactive, 'viewable')->create();
        PageView::factory(5)->for($draft, 'viewable')->create();

        $posts = app(PostService::class)->filter([
            'user' => $user,
            'active' => true,
            'status' => 'published',
            'viewed_since' => now()->subWeek(),
            'order_by' => 'views_count',
            'order_direction' => 'desc',
            'ignore_authorization' => true,
        ]);

        $this->assertSame(
            [$mostViewed->id, $secondMostViewed->id],
            $posts->pluck('id')->all()
        );
        $this->assertSame([4, 2], $posts->pluck('views_count')->all());
    }
}
