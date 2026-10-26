<?php

namespace Tests\Feature\Models;

use App\Models\Onair;
use App\Models\Post;
use App\Models\Program;
use App\Models\SocialPublicationSchedule;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialPublicationScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_preserves_the_publication_snapshot_and_result(): void
    {
        $post = Post::factory()->create();
        $schedule = SocialPublicationSchedule::factory()->published()->for($post, 'publishable')->create([
            'payload' => [
                'caption' => 'Nova matéria na Rede Akiba',
                'hashtags' => ['Akiba', 'Anime'],
                'post_url' => 'https://akiba.test/materia',
            ],
            'channel' => 'instagram',
            'placement' => 'feed',
            'external_id' => 'instagram-media-id',
            'scheduled_at' => '2026-10-21 10:00:00',
            'published_at' => '2026-10-21 10:05:00',
        ]);

        $this->assertTrue($schedule->publishable->is($post));
        $this->assertTrue($post->socialPublicationSchedules->first()->is($schedule));
        $this->assertSame(['Akiba', 'Anime'], $schedule->payload['hashtags']);
        $this->assertSame(SocialPublicationSchedule::STATUS_PUBLISHED, $schedule->status);
        $this->assertSame('feed', $schedule->placement);
        $this->assertSame('2026-10-21 10:00:00', $schedule->scheduled_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-21 10:05:00', $schedule->published_at->format('Y-m-d H:i:s'));
    }

    public function test_it_supports_different_publishable_models(): void
    {
        $program = Program::factory()->for(User::factory(), 'host')->create();
        $onair = Onair::factory()->for($program)->create();
        $schedule = SocialPublicationSchedule::factory()->for($onair, 'publishable')->create();

        $this->assertTrue($schedule->publishable->is($onair));
        $this->assertTrue($onair->socialPublicationSchedules->first()->is($schedule));
    }

    public function test_it_prevents_a_duplicate_idempotency_key(): void
    {
        SocialPublicationSchedule::factory()->create(['idempotency_key' => 'social:duplicate']);

        $this->expectException(QueryException::class);

        SocialPublicationSchedule::factory()->create(['idempotency_key' => 'social:duplicate']);
    }

    public function test_feed_and_story_are_stored_as_independent_schedules(): void
    {
        $post = Post::factory()->create();

        SocialPublicationSchedule::factory()->for($post, 'publishable')->create([
            'placement' => 'feed',
        ]);
        SocialPublicationSchedule::factory()->for($post, 'publishable')->create([
            'placement' => 'story',
        ]);

        $this->assertSame(
            ['feed', 'story'],
            $post->socialPublicationSchedules()->orderBy('placement')->pluck('placement')->all(),
        );
    }
}
