<?php

namespace Tests\Feature\Jobs;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationData;
use App\Integrations\MetaSocial\PublicationException;
use App\Integrations\MetaSocial\PublicationFailure;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Integrations\MetaSocial\PublicationResult;
use App\Integrations\MetaSocial\Publisher;
use App\Jobs\PublishSocialPublicationSchedule;
use App\Models\SocialPublicationSchedule;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublishSocialPublicationScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_publishes_a_schedule_once_and_removes_its_artwork(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('social-artworks/post/feed.jpg', 'image');
        $schedule = $this->pendingSchedule();
        $publisher = new SuccessfulSocialPublisherFake;
        $job = new PublishSocialPublicationSchedule($schedule->id);

        $job->handle($publisher);
        $job->handle($publisher);
        $schedule->refresh();

        $this->assertSame(SocialPublicationSchedule::STATUS_PUBLISHED, $schedule->status);
        $this->assertSame('external-instagram-id', $schedule->external_id);
        $this->assertNotNull($schedule->published_at);
        $this->assertSame(1, $publisher->calls);
        $this->assertSame($schedule->idempotency_key, $publisher->publication?->idempotencyKey);
        $this->assertSame(PublicationPlacement::Feed, $publisher->publication?->placement);
        $this->assertFalse($publisher->publication?->linkPreview);
        Storage::disk('public')->assertMissing('social-artworks/post/feed.jpg');
    }

    public function test_a_definitive_failure_marks_the_schedule_as_failed_and_keeps_the_artwork(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('social-artworks/post/feed.jpg', 'image');
        $schedule = $this->pendingSchedule();

        (new PublishSocialPublicationSchedule($schedule->id))
            ->handle(new FailingSocialPublisherFake(PublicationFailure::Permission));

        $schedule->refresh();
        $this->assertSame(SocialPublicationSchedule::STATUS_FAILED, $schedule->status);
        $this->assertSame('Meta Graph API request failed (permission).', $schedule->last_error);
        Storage::disk('public')->assertExists('social-artworks/post/feed.jpg');
    }

    public function test_a_recoverable_failure_is_left_pending_until_the_queue_exhausts_retries(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('social-artworks/post/feed.jpg', 'image');
        $schedule = $this->pendingSchedule();
        $job = new PublishSocialPublicationSchedule($schedule->id);
        $exception = new PublicationException(
            message: 'Meta Graph API is temporarily unavailable.',
            failure: PublicationFailure::Temporary,
        );

        try {
            $job->handle(new FailingSocialPublisherFake(PublicationFailure::Temporary));
            $this->fail('Expected the retryable publication failure to be rethrown.');
        } catch (PublicationException $caught) {
            $this->assertTrue($caught->isRetryable());
        }

        $this->assertSame(SocialPublicationSchedule::STATUS_PENDING, $schedule->refresh()->status);
        Storage::disk('public')->assertExists('social-artworks/post/feed.jpg');

        $job->failed($exception);

        $this->assertSame(SocialPublicationSchedule::STATUS_FAILED, $schedule->refresh()->status);
        $this->assertSame([60, 300, 900], $job->backoff());
        $this->assertSame(4, $job->tries);
        Storage::disk('public')->assertExists('social-artworks/post/feed.jpg');
    }

    private function pendingSchedule(): SocialPublicationSchedule
    {
        return SocialPublicationSchedule::factory()->create([
            'channel' => 'instagram',
            'placement' => 'feed',
            'payload' => [
                'caption' => 'Nova matéria',
                'hashtags' => ['Akiba', 'Anime'],
                'post_url' => 'https://akiba.test/materia',
            ],
            'media_path' => 'social-artworks/post/feed.jpg',
            'media_url' => 'https://akiba.test/storage/social-artworks/post/feed.jpg',
            'status' => SocialPublicationSchedule::STATUS_PENDING,
        ]);
    }
}

class SuccessfulSocialPublisherFake implements Publisher
{
    public int $calls = 0;

    public ?PublicationData $publication = null;

    public function publish(PublicationChannel $channel, PublicationData $publication): PublicationResult
    {
        $this->calls++;
        $this->publication = $publication;

        return new PublicationResult(
            channel: $channel,
            externalId: 'external-instagram-id',
            publishedAt: new DateTimeImmutable('2026-10-21T12:00:00-03:00'),
        );
    }
}

class FailingSocialPublisherFake implements Publisher
{
    public function __construct(private readonly PublicationFailure $failure) {}

    public function publish(PublicationChannel $channel, PublicationData $publication): PublicationResult
    {
        throw new PublicationException(
            message: "Meta Graph API request failed ({$this->failure->value}).",
            failure: $this->failure,
        );
    }
}
