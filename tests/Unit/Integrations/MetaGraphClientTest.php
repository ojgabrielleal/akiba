<?php

namespace Tests\Unit\Integrations;

use App\Integrations\MetaSocial\GraphClient;
use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationConfiguration;
use App\Integrations\MetaSocial\PublicationData;
use App\Integrations\MetaSocial\PublicationException;
use App\Integrations\MetaSocial\PublicationFailure;
use App\Integrations\MetaSocial\PublicationPlacement;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaGraphClientTest extends TestCase
{
    private GraphClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        config([
            'services.meta.graph_version' => 'v24.0',
            'services.meta.instagram_user_id' => 'instagram-user-id',
            'services.meta.access_token' => 'secret-access-token',
            'services.meta.timeout' => 15,
            'services.meta.connect_timeout' => 5,
            'services.meta.container_poll_attempts' => 5,
            'services.meta.container_poll_interval_ms' => 0,
            'services.meta.instagram_enabled' => true,
        ]);

        $this->client = new GraphClient(new PublicationConfiguration);
    }

    public function test_it_creates_checks_and_publishes_an_instagram_container(): void
    {
        Http::fakeSequence()
            ->push(['id' => 'container-id'])
            ->push(['id' => 'container-id', 'status_code' => 'FINISHED', 'status' => 'Finished'])
            ->push(['id' => 'instagram-media-id']);

        $result = $this->client->publish(PublicationChannel::Instagram, $this->publication());

        $this->assertSame(PublicationChannel::Instagram, $result->channel);
        $this->assertSame('instagram-media-id', $result->externalId);
        Http::assertSentInOrder([
            fn (Request $request): bool => $request->method() === 'POST'
                && $request->url() === 'https://graph.instagram.com/v24.0/instagram-user-id/media'
                && $request->hasHeader('Authorization', 'Bearer secret-access-token')
                && $request['image_url'] === 'https://akiba.test/social/post.jpg'
                && ! isset($request['media_type'])
                && $request['caption'] === "Nova matéria\n\n#Akiba #Anime",
            fn (Request $request): bool => $request->method() === 'GET'
                && $request->url() === 'https://graph.instagram.com/v24.0/container-id?fields=status_code%2Cstatus',
            fn (Request $request): bool => $request->method() === 'POST'
                && $request->url() === 'https://graph.instagram.com/v24.0/instagram-user-id/media_publish'
                && $request['creation_id'] === 'container-id',
        ]);
    }

    public function test_it_creates_and_publishes_an_instagram_story_without_a_feed_caption(): void
    {
        Http::fakeSequence()
            ->push(['id' => 'story-container-id'])
            ->push(['id' => 'story-container-id', 'status_code' => 'FINISHED'])
            ->push(['id' => 'instagram-story-id']);

        $result = $this->client->publish(
            PublicationChannel::Instagram,
            $this->publication(PublicationPlacement::Story),
        );

        $this->assertSame('instagram-story-id', $result->externalId);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://graph.instagram.com/v24.0/instagram-user-id/media'
            && $request['image_url'] === 'https://akiba.test/social/post.jpg'
            && $request['media_type'] === 'STORIES'
            && ! isset($request['caption']));
    }

    public function test_it_waits_for_the_same_container_to_finish_before_publishing(): void
    {
        Http::fakeSequence()
            ->push(['id' => 'container-id'])
            ->push(['status_code' => 'IN_PROGRESS'])
            ->push(['status_code' => 'IN_PROGRESS'])
            ->push(['status_code' => 'FINISHED'])
            ->push(['id' => 'instagram-media-id']);

        $result = $this->client->publish(PublicationChannel::Instagram, $this->publication());

        $this->assertSame('instagram-media-id', $result->externalId);
        Http::assertSentCount(5);
    }

    public function test_it_normalizes_authorization_errors_without_exposing_sensitive_data(): void
    {
        Http::fake([
            '*' => Http::response([
                'error' => [
                    'message' => 'Invalid token secret-access-token for caption Nova matéria',
                    'type' => 'OAuthException',
                    'code' => 190,
                ],
            ], 401),
        ]);

        try {
            $this->client->publish(PublicationChannel::Instagram, $this->publication());
            $this->fail('Expected the Graph API authorization error to be normalized.');
        } catch (PublicationException $exception) {
            $this->assertSame(PublicationFailure::Authentication, $exception->failure);
            $this->assertSame(401, $exception->httpStatus);
            $this->assertSame('190', $exception->providerCode);
            $this->assertFalse($exception->isRetryable());
            $this->assertStringNotContainsString('secret-access-token', $exception->getMessage());
            $this->assertStringNotContainsString('Nova matéria', $exception->getMessage());
        }
    }

    public function test_it_rejects_an_invalid_success_response(): void
    {
        Http::fake(['*' => Http::response(['unexpected' => true])]);

        try {
            $this->client->publish(PublicationChannel::Instagram, $this->publication());
            $this->fail('Expected an invalid Graph API response to be rejected.');
        } catch (PublicationException $exception) {
            $this->assertSame(PublicationFailure::Unknown, $exception->failure);
            $this->assertSame(200, $exception->httpStatus);
            $this->assertFalse($exception->isRetryable());
        }
    }

    public function test_it_marks_connection_failures_as_retryable(): void
    {
        Http::fake(fn () => Http::failedConnection());

        try {
            $this->client->publish(PublicationChannel::Instagram, $this->publication());
            $this->fail('Expected a connection failure to be normalized.');
        } catch (PublicationException $exception) {
            $this->assertSame(PublicationFailure::Temporary, $exception->failure);
            $this->assertTrue($exception->isRetryable());
            $this->assertNull($exception->httpStatus);
        }
    }

    public function test_it_marks_an_unavailable_graph_api_as_retryable(): void
    {
        Http::fake(['*' => Http::response('Service unavailable', 503)]);

        try {
            $this->client->publish(PublicationChannel::Instagram, $this->publication());
            $this->fail('Expected an unavailable Graph API response to be normalized.');
        } catch (PublicationException $exception) {
            $this->assertSame(PublicationFailure::Temporary, $exception->failure);
            $this->assertSame(503, $exception->httpStatus);
            $this->assertTrue($exception->isRetryable());
        }
    }

    private function publication(PublicationPlacement $placement = PublicationPlacement::Feed): PublicationData
    {
        return new PublicationData(
            caption: 'Nova matéria',
            hashtags: ['Akiba', '#Anime'],
            imageUrl: 'https://akiba.test/social/post.jpg',
            postUrl: 'https://akiba.test/posts/nova-materia',
            idempotencyKey: "meta:post:publication:instagram:{$placement->value}",
            placement: $placement,
        );
    }
}
