<?php

namespace Tests\Feature\Services;

use App\Jobs\PublishSocialPublicationSchedule;
use App\Models\Onair;
use App\Models\Program;
use App\Models\User;
use App\Processing\SocialArtworkProcess;
use App\Processing\SocialArtworkResult;
use App\Services\SocialPublicationScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class LiveSocialPublicationOrchestrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_schedules_live_content_through_the_shared_social_flow(): void
    {
        Queue::fake();
        config([
            'services.meta.instagram_enabled' => true,
            'services.meta.instagram_feed_enabled' => true,
        ]);
        $this->mock(SocialArtworkProcess::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generate')
                ->once()
                ->withArgs(fn (string $template): bool => $template === 'instagram-live-feed')
                ->andReturn(new SocialArtworkResult(
                    template: 'instagram-live-feed',
                    path: 'social-artworks/live/feed.jpg',
                    url: 'https://akiba.test/storage/social-artworks/live/feed.jpg',
                    mimeType: 'image/jpeg',
                    width: 1080,
                    height: 1350,
                    size: 100,
                ));
        });
        $host = User::factory()->create();
        $program = Program::factory()->for($host, 'host')->create();
        $onair = Onair::factory()->for($program)->for($host, 'host')->live()->create();

        app(SocialPublicationScheduleService::class)->scheduleLive($onair, [
            'text' => 'O Conexão Akiba está no ar!',
            'hashtags' => ['radio', 'anime'],
        ]);

        $schedule = $onair->socialPublicationSchedules()->firstOrFail();
        $this->assertSame('instagram', $schedule->channel);
        $this->assertSame('feed', $schedule->placement);
        $this->assertSame(['radio', 'anime'], $schedule->payload['hashtags']);
        $this->assertStringStartsWith('social:Onair:', $schedule->idempotency_key);
        Queue::assertPushed(PublishSocialPublicationSchedule::class, 1);
    }
}
