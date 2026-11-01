<?php

namespace Tests\Feature\Public;

use App\Integrations\IpLocation;
use App\Models\Post;
use App\Models\SocialPublicationSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InstagramStoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_lists_only_public_posts_with_successful_instagram_stories_in_promotion_order(): void
    {
        $older = Post::factory()->create(['status' => 'published', 'title' => 'Matéria anterior']);
        $latest = Post::factory()->create(['status' => 'published', 'title' => 'Matéria mais recente']);
        $draft = Post::factory()->create(['status' => 'draft']);
        $failed = Post::factory()->create(['status' => 'published']);

        $this->story($older, SocialPublicationSchedule::STATUS_PUBLISHED, now()->subHour());
        $this->story($latest, SocialPublicationSchedule::STATUS_PUBLISHED, now());
        $this->story($draft, SocialPublicationSchedule::STATUS_PUBLISHED, now()->addMinute());
        $this->story($failed, SocialPublicationSchedule::STATUS_FAILED, null);

        $this->get('/stories')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/Instagram', false)
                ->has('stories', 2)
                ->where('stories.0.title', 'Matéria mais recente')
                ->where('stories.1.title', 'Matéria anterior'));
    }

    public function test_promoted_post_redirect_tracks_the_click_and_preserves_campaign_parameters(): void
    {
        $this->mock(IpLocation::class, fn ($mock) => $mock
            ->shouldReceive('find')
            ->once()
            ->andReturn([]));
        $post = Post::factory()->create(['status' => 'published']);
        $this->story($post, SocialPublicationSchedule::STATUS_PUBLISHED, now());

        $response = $this->get(route('stories.post.redirect', [
            'post' => $post->uuid,
            'utm_source' => 'instagram',
            'utm_medium' => 'story',
            'utm_campaign' => 'materias',
        ]));

        $response->assertRedirect(route('post.read', [
            'slug' => $post->slug,
            'utm_source' => 'instagram',
            'utm_medium' => 'story',
            'utm_campaign' => 'materias',
        ]));
        $this->assertDatabaseHas('page_views', [
            'viewable_type' => Post::class,
            'viewable_id' => $post->id,
            'utm_source' => 'instagram',
            'utm_medium' => 'story',
            'utm_campaign' => 'materias',
        ]);
    }

    public function test_unpromoted_post_cannot_use_the_tracking_redirect(): void
    {
        $post = Post::factory()->create(['status' => 'published']);

        $this->get(route('stories.post.redirect', $post->uuid))->assertNotFound();
    }

    private function story(Post $post, string $status, mixed $publishedAt): SocialPublicationSchedule
    {
        return $post->socialPublicationSchedules()->create([
            'channel' => 'instagram',
            'placement' => 'story',
            'payload' => [],
            'media_url' => 'https://cdn.example.com/story.jpg',
            'status' => $status,
            'idempotency_key' => 'test:'.$post->uuid.':'.uniqid(),
            'scheduled_at' => now(),
            'published_at' => $publishedAt,
        ]);
    }
}
