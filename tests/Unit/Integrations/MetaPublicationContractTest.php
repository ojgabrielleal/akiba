<?php

namespace Tests\Unit\Integrations;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationData;
use App\Integrations\MetaSocial\PublicationException;
use App\Integrations\MetaSocial\PublicationFailure;
use App\Integrations\MetaSocial\PublicationIdempotencyKey;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Integrations\MetaSocial\PublicationResult;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class MetaPublicationContractTest extends TestCase
{
    public function test_it_represents_the_common_publication_payload(): void
    {
        $publication = new PublicationData(
            caption: 'Nova matéria',
            hashtags: ['Akiba', 'Anime'],
            imageUrl: 'https://akiba.test/social/post.jpg',
            postUrl: 'https://akiba.test/materia/nova-materia',
            idempotencyKey: 'meta:post-uuid:publication-uuid:instagram:feed',
        );

        $this->assertSame('Nova matéria', $publication->caption);
        $this->assertSame(['Akiba', 'Anime'], $publication->hashtags);
        $this->assertSame('https://akiba.test/social/post.jpg', $publication->imageUrl);
        $this->assertSame('https://akiba.test/materia/nova-materia', $publication->postUrl);
        $this->assertSame('meta:post-uuid:publication-uuid:instagram:feed', $publication->idempotencyKey);
        $this->assertSame(PublicationPlacement::Feed, $publication->placement);
    }

    public function test_it_represents_a_normalized_publication_result(): void
    {
        $publishedAt = new DateTimeImmutable('2026-10-21T10:00:00-03:00');
        $result = new PublicationResult(
            channel: PublicationChannel::Instagram,
            externalId: '17890000000000000',
            publishedAt: $publishedAt,
            permalink: 'https://www.instagram.com/p/example/',
        );

        $this->assertSame(PublicationChannel::Instagram, $result->channel);
        $this->assertSame('17890000000000000', $result->externalId);
        $this->assertSame($publishedAt, $result->publishedAt);
        $this->assertSame('https://www.instagram.com/p/example/', $result->permalink);
    }

    public function test_it_builds_an_extensible_idempotency_key_with_the_channel(): void
    {
        $instagram = PublicationIdempotencyKey::make(
            'post-uuid',
            'publication-uuid',
            PublicationChannel::Instagram,
        );

        $this->assertSame('meta:post-uuid:publication-uuid:instagram:feed', $instagram);

        $instagramStory = PublicationIdempotencyKey::make(
            'post-uuid',
            'publication-uuid',
            PublicationChannel::Instagram,
            PublicationPlacement::Story,
        );

        $this->assertSame('meta:post-uuid:publication-uuid:instagram:story', $instagramStory);

        $facebook = PublicationIdempotencyKey::make(
            'post-uuid',
            'publication-uuid',
            PublicationChannel::Facebook,
        );

        $this->assertSame('meta:post-uuid:publication-uuid:facebook:feed', $facebook);
    }

    public function test_only_rate_limits_and_temporary_failures_are_retryable(): void
    {
        $this->assertTrue(PublicationFailure::RateLimit->isRetryable());
        $this->assertTrue(PublicationFailure::Temporary->isRetryable());
        $this->assertFalse(PublicationFailure::Authentication->isRetryable());
        $this->assertFalse(PublicationFailure::Permission->isRetryable());
        $this->assertFalse(PublicationFailure::Validation->isRetryable());
        $this->assertFalse(PublicationFailure::Unknown->isRetryable());

        $exception = new PublicationException(
            message: 'Meta is temporarily unavailable.',
            failure: PublicationFailure::Temporary,
            httpStatus: 503,
            providerCode: '2',
        );

        $this->assertTrue($exception->isRetryable());
        $this->assertSame(503, $exception->httpStatus);
        $this->assertSame('2', $exception->providerCode);
    }
}
