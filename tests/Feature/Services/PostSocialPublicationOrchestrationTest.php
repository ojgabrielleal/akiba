<?php

namespace Tests\Feature\Services;

use App\Jobs\PublishPostSocialChannel;
use App\Models\Post;
use App\Models\User;
use App\Processing\SocialArtworkProcess;
use App\Services\PostService;
use App\Services\PostSocialPublicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class PostSocialPublicationOrchestrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Storage::fake('public');
        config([
            'services.meta.instagram_enabled' => true,
            'services.meta.instagram_feed_enabled' => true,
        ]);
    }

    public function test_manual_approval_and_scheduled_flows_each_enqueue_instagram_once(): void
    {
        $service = app(PostService::class);
        $user = User::factory()->create();

        $manual = $service->store($user, [
            'module' => 'post',
            'status' => 'published',
            'title' => 'Publicação manual',
            'content' => 'Conteúdo manual',
            'tags' => [['name' => 'news']],
            'social' => [
                'text' => 'Legenda editorial para o Instagram.',
                'hashtags' => ['anime', 'redeakiba'],
                'channels' => ['instagram' => true],
            ],
        ]);

        $approval = Post::factory()->for($user, 'author')->create([
            'status' => 'revision',
            'title' => 'Publicação aprovada',
        ]);
        $service->update($approval, $user, [
            'module' => 'post',
            'status' => 'published',
            'title' => $approval->title,
            'content' => $approval->content,
            'tags' => [],
        ]);

        $scheduled = Post::factory()->for($user, 'author')->create([
            'status' => 'revision',
            'title' => 'Publicação agendada',
            'updated_at' => now()->subHours(2),
        ]);
        Artisan::call('posts:publish-reviewed', ['--hours' => 1, '--news-hours' => 1]);

        $this->assertDatabaseCount('post_social_publications', 3);
        $this->assertDatabaseCount('post_social_publication_results', 3);
        $this->assertCount(1, $manual->socialPublications()->firstOrFail()->results);
        $this->assertCount(1, $approval->socialPublications()->firstOrFail()->results);
        $this->assertCount(1, $scheduled->socialPublications()->firstOrFail()->results);
        $this->assertSame(
            ['instagram'],
            $manual->socialPublications()->firstOrFail()->results()->orderBy('channel')->pluck('channel')->all(),
        );
        $this->assertSame(
            ['feed'],
            $manual->socialPublications()->firstOrFail()->results()->pluck('placement')->all(),
        );
        $manualResult = $manual->socialPublications()->firstOrFail()->results()->firstOrFail();
        $manualPublication = $manual->socialPublications()->firstOrFail();
        $this->assertSame('Legenda editorial para o Instagram.', $manualPublication->caption);
        $this->assertSame(['anime', 'redeakiba'], $manualPublication->hashtags);
        $this->assertStringContainsString('/storage/social-artworks/', $manualResult->artwork_path);
        Storage::disk('public')->assertExists(
            Str::after(parse_url($manualResult->artwork_path, PHP_URL_PATH), '/storage/'),
        );
        Queue::assertPushed(PublishPostSocialChannel::class, 3);

        $service->update($manual, $user, [
            'module' => 'post',
            'status' => 'published',
            'title' => 'Publicação manual atualizada',
            'content' => 'Conteúdo atualizado',
            'tags' => [],
        ]);

        $this->assertDatabaseCount('post_social_publications', 3);
        Queue::assertPushed(PublishPostSocialChannel::class, 3);
    }

    public function test_disabled_channels_do_not_create_a_publication_or_job(): void
    {
        config([
            'services.meta.instagram_enabled' => false,
        ]);

        app(PostService::class)->store(User::factory()->create(), [
            'module' => 'post',
            'status' => 'published',
            'title' => 'Sem divulgação',
            'content' => 'Conteúdo',
            'tags' => [],
        ]);

        $this->assertDatabaseCount('post_social_publications', 0);
        Queue::assertNothingPushed();
    }

    public function test_disabling_the_feed_does_not_create_a_social_publication(): void
    {
        config([
            'services.meta.instagram_feed_enabled' => false,
        ]);

        app(PostService::class)->store(User::factory()->create(), [
            'module' => 'post',
            'status' => 'published',
            'title' => 'Feed desabilitado',
            'content' => 'Conteúdo',
            'tags' => [],
        ]);

        $this->assertDatabaseCount('post_social_publications', 0);
        $this->assertDatabaseCount('post_social_publication_results', 0);
        Queue::assertNothingPushed();
    }

    public function test_artwork_failure_is_logged_and_does_not_rollback_the_published_post(): void
    {
        $this->mock(SocialArtworkProcess::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generate')
                ->once()
                ->andThrow(new RuntimeException('GD unavailable'));
        });

        $post = Post::factory()->create([
            'title' => 'Publicação com fallback',
            'status' => 'published',
            'cover' => 'https://cdn.example.com/cover.webp',
        ]);

        app(PostSocialPublicationService::class)->prepare($post);

        $this->assertSame('published', $post->fresh()->status);
        $result = $post->socialPublications()->firstOrFail()->results()->firstOrFail();
        $this->assertSame(route('post.social-image', $post->uuid), $result->artwork_path);
        Queue::assertPushed(PublishPostSocialChannel::class, 1);
    }
}
