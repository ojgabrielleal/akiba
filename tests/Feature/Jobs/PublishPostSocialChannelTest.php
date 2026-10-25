<?php

namespace Tests\Feature\Jobs;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationData;
use App\Integrations\MetaSocial\PublicationException;
use App\Integrations\MetaSocial\PublicationFailure;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Integrations\MetaSocial\PublicationResult;
use App\Integrations\MetaSocial\Publisher;
use App\Jobs\PublishPostSocialChannel;
use App\Models\Post;
use App\Models\PostSocialPublication;
use App\Models\PostSocialPublicationResult;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishPostSocialChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_publishes_one_channel_and_preserves_the_audit_result(): void
    {
        $result = $this->pendingResult('instagram');
        $publisher = new SuccessfulPublisherFake;
        $job = new PublishPostSocialChannel($result->id);

        $job->handle($publisher);
        $job->handle($publisher);

        $result->refresh();

        $this->assertSame(PostSocialPublicationResult::STATUS_PUBLISHED, $result->status);
        $this->assertSame('external-instagram-id', $result->external_id);
        $this->assertSame(1, $result->attempts);
        $this->assertNotNull($result->published_at);
        $this->assertSame(1, $publisher->calls);
        $this->assertSame(PublicationChannel::Instagram, $publisher->channel);
        $this->assertSame(
            "meta:{$result->publication->post->uuid}:{$result->publication->uuid}:instagram:feed",
            $publisher->publication?->idempotencyKey,
        );
        $this->assertSame(PublicationPlacement::Feed, $publisher->publication?->placement);
    }

    public function test_a_definitive_failure_marks_only_that_channel_as_failed_without_throwing(): void
    {
        $result = $this->pendingResult('instagram');
        $job = new PublishPostSocialChannel($result->id);

        $job->handle(new FailingPublisherFake(PublicationFailure::Permission));

        $result->refresh();

        $this->assertSame(PostSocialPublicationResult::STATUS_FAILED, $result->status);
        $this->assertSame(1, $result->attempts);
        $this->assertSame('Meta Graph API request failed (permission).', $result->error);
        $this->assertSame('published', $result->publication->post->status);
    }

    public function test_a_recoverable_failure_is_rethrown_for_the_queue_and_fails_after_final_attempt(): void
    {
        $result = $this->pendingResult('instagram');
        $job = new PublishPostSocialChannel($result->id);
        $exception = new PublicationException(
            message: 'Meta Graph API is temporarily unavailable.',
            failure: PublicationFailure::Temporary,
        );

        try {
            $job->handle(new FailingPublisherFake(PublicationFailure::Temporary));
            $this->fail('Expected the retryable publication failure to be rethrown.');
        } catch (PublicationException $caught) {
            $this->assertTrue($caught->isRetryable());
        }

        $this->assertSame(PostSocialPublicationResult::STATUS_PENDING, $result->refresh()->status);
        $this->assertSame(1, $result->attempts);

        $job->failed($exception);

        $this->assertSame(PostSocialPublicationResult::STATUS_FAILED, $result->refresh()->status);
        $this->assertSame('Meta Graph API is temporarily unavailable.', $result->error);
        $this->assertSame([60, 300, 900], $job->backoff());
        $this->assertSame(4, $job->tries);
    }

    private function pendingResult(string $channel): PostSocialPublicationResult
    {
        $post = Post::factory()->create(['status' => 'published']);
        $publication = PostSocialPublication::factory()->for($post)->create([
            'caption' => 'Nova matéria',
            'hashtags' => ['Akiba', 'Anime'],
            'artwork_path' => 'https://akiba.test/social/test-image.jpg',
        ]);

        return PostSocialPublicationResult::factory()->for($publication, 'publication')->create([
            'channel' => $channel,
            'placement' => 'feed',
            'artwork_path' => 'https://akiba.test/social/test-image.jpg',
            'status' => PostSocialPublicationResult::STATUS_PENDING,
        ]);
    }
}

class SuccessfulPublisherFake implements Publisher
{
    public int $calls = 0;

    public ?PublicationChannel $channel = null;

    public ?PublicationData $publication = null;

    public function publish(PublicationChannel $channel, PublicationData $publication): PublicationResult
    {
        $this->calls++;
        $this->channel = $channel;
        $this->publication = $publication;

        return new PublicationResult(
            channel: $channel,
            externalId: 'external-'.$channel->value.'-id',
            publishedAt: new DateTimeImmutable('2026-10-21T12:00:00-03:00'),
        );
    }
}

class FailingPublisherFake implements Publisher
{
    public function __construct(
        private readonly PublicationFailure $failure,
    ) {}

    public function publish(PublicationChannel $channel, PublicationData $publication): PublicationResult
    {
        throw new PublicationException(
            message: "Meta Graph API request failed ({$this->failure->value}).",
            failure: $this->failure,
        );
    }
}
