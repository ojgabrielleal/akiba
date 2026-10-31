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
            'services.meta.facebook_enabled' => true,
            'services.meta.facebook_feed_enabled' => true,
        ]);
        $this->mock(SocialArtworkProcess::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generate')
                ->twice()
                ->withArgs(fn (string $template): bool => in_array($template, [
                    'instagram-live-feed',
                    'facebook-live-feed',
                ], true))
                ->andReturnUsing(fn (string $template): SocialArtworkResult => new SocialArtworkResult(
                    template: $template,
                    path: "social-artworks/live/{$template}.jpg",
                    url: "https://akiba.test/storage/social-artworks/live/{$template}.jpg",
                    mimeType: 'image/jpeg',
                    width: 1080,
                    height: $template === 'facebook-live-feed' ? 1080 : 1350,
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

        $schedules = $onair->socialPublicationSchedules()->orderBy('channel')->get();
        $this->assertSame(['facebook', 'instagram'], $schedules->pluck('channel')->all());
        $this->assertSame(['feed', 'feed'], $schedules->pluck('placement')->all());
        $this->assertSame($schedules[0]->payload, $schedules[1]->payload);
        $this->assertSame(implode("\n\n", [
            'O Conexão Akiba está no ar!',
            "🎧 Ouça agora:\nhttps://akiba.com.br",
            "💬 Venha para a comunidade da Rede Akiba no Discord e converse com gente que te entende:\nhttps://discord.akiba.com.br",
        ]), $schedules[0]->payload['caption']);
        $this->assertSame(['radio', 'anime'], $schedules[0]->payload['hashtags']);
        $this->assertStringStartsWith('social:Onair:', $schedules[0]->idempotency_key);
        Queue::assertPushed(PublishSocialPublicationSchedule::class, 2);
    }
}
