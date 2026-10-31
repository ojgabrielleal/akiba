<?php

namespace Tests\Feature\Services;

use App\Jobs\PublishSocialPublicationSchedule;
use App\Models\Post;
use App\Models\User;
use App\Processing\SocialArtworkProcess;
use App\Services\PostService;
use App\Services\SocialPublicationScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
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
            'services.meta.facebook_enabled' => false,
            'services.meta.facebook_feed_enabled' => true,
        ]);
    }

    public function test_manual_approval_and_scheduled_flows_enqueue_instagram_and_facebook_once(): void
    {
        config([
            'services.meta.facebook_enabled' => true,
            'services.meta.facebook_feed_enabled' => true,
        ]);
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

        $this->assertDatabaseCount('social_publication_schedules', 6);
        $this->assertCount(2, $manual->socialPublicationSchedules);
        $this->assertCount(2, $approval->socialPublicationSchedules);
        $this->assertCount(2, $scheduled->socialPublicationSchedules);
        $this->assertSame(
            ['facebook', 'instagram'],
            $manual->socialPublicationSchedules()->orderBy('channel')->pluck('channel')->all(),
        );
        $this->assertSame(
            ['feed', 'feed'],
            $manual->socialPublicationSchedules()->orderBy('channel')->pluck('placement')->all(),
        );
        $manualSchedule = $manual->socialPublicationSchedules()->where('channel', 'instagram')->firstOrFail();
        $facebookSchedule = $manual->socialPublicationSchedules()->where('channel', 'facebook')->firstOrFail();
        $this->assertSame(implode("\n\n", [
            'Legenda editorial para o Instagram.',
            "🔗 Saiba mais:\n".route('post.short', $manual->short_code),
            "💬 Venha para a comunidade da Rede Akiba no Discord e converse com gente que te entende:\nhttps://discord.akiba.com.br",
        ]), $manualSchedule->payload['caption']);
        $this->assertSame(['anime', 'redeakiba'], $manualSchedule->payload['hashtags']);
        $this->assertSame($manualSchedule->payload['caption'], $facebookSchedule->payload['caption']);
        $this->assertSame($manualSchedule->payload['hashtags'], $facebookSchedule->payload['hashtags']);
        $this->assertFalse($manualSchedule->payload['link_preview']);
        $this->assertFalse($facebookSchedule->payload['link_preview']);
        $this->assertStringContainsString('/storage/social-artworks/', $manualSchedule->media_url);
        $this->assertStringContainsString('/storage/social-artworks/', $facebookSchedule->media_url);
        $this->assertStringContainsString('instagram-feed-', $manualSchedule->media_path);
        $this->assertStringContainsString('facebook-feed-', $facebookSchedule->media_path);
        Storage::disk('public')->assertExists($manualSchedule->media_path);
        Storage::disk('public')->assertExists($facebookSchedule->media_path);
        Queue::assertPushed(PublishSocialPublicationSchedule::class, 6);

        $service->update($manual, $user, [
            'module' => 'post',
            'status' => 'published',
            'title' => 'Publicação manual atualizada',
            'content' => 'Conteúdo atualizado',
            'tags' => [],
        ]);

        $this->assertDatabaseCount('social_publication_schedules', 6);
        Queue::assertPushed(PublishSocialPublicationSchedule::class, 6);
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

        $this->assertDatabaseCount('social_publication_schedules', 0);
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

        $this->assertDatabaseCount('social_publication_schedules', 0);
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

        app(SocialPublicationScheduleService::class)->schedulePost($post);

        $this->assertSame('published', $post->fresh()->status);
        $schedule = $post->socialPublicationSchedules()->firstOrFail();
        $this->assertSame(route('post.social-image', $post->uuid), $schedule->media_url);
        $this->assertNull($schedule->media_path);
        Queue::assertPushed(PublishSocialPublicationSchedule::class, 1);
    }
}
