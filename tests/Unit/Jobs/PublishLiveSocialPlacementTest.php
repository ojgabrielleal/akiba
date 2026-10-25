<?php

namespace Tests\Unit\Jobs;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationData;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Integrations\MetaSocial\PublicationResult;
use App\Integrations\MetaSocial\Publisher;
use App\Jobs\PublishLiveSocialPlacement;
use DateTimeImmutable;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublishLiveSocialPlacementTest extends TestCase
{
    public function test_it_publishes_live_social_data_and_removes_the_temporary_artwork(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('social-artworks/live/story.jpg', 'image');

        $publisher = new LivePublisherFake;
        $job = new PublishLiveSocialPlacement(
            onairUuid: 'onair-uuid',
            publicationUuid: 'publication-uuid',
            placement: PublicationPlacement::Story,
            caption: 'O Conexão Akiba está no ar!',
            hashtags: ['radio', 'anime'],
            imageUrl: 'https://akiba.test/storage/social-artworks/live/story.jpg',
            imagePath: 'social-artworks/live/story.jpg',
        );

        $job->handle($publisher);

        $this->assertSame(PublicationChannel::Instagram, $publisher->channel);
        $this->assertSame(PublicationPlacement::Story, $publisher->publication?->placement);
        $this->assertSame('O Conexão Akiba está no ar!', $publisher->publication?->caption);
        $this->assertSame(['radio', 'anime'], $publisher->publication?->hashtags);
        $this->assertSame(
            'meta:live:onair-uuid:publication-uuid:story',
            $publisher->publication?->idempotencyKey,
        );
        $this->assertSame([60, 300, 900], $job->backoff());
        $this->assertSame(4, $job->tries);
        Storage::disk('public')->assertMissing('social-artworks/live/story.jpg');
    }
}

class LivePublisherFake implements Publisher
{
    public ?PublicationChannel $channel = null;

    public ?PublicationData $publication = null;

    public function publish(PublicationChannel $channel, PublicationData $publication): PublicationResult
    {
        $this->channel = $channel;
        $this->publication = $publication;

        return new PublicationResult(
            channel: $channel,
            externalId: 'live-publication-id',
            publishedAt: new DateTimeImmutable('2026-10-24T20:00:00-03:00'),
        );
    }
}
