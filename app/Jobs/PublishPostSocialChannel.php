<?php

namespace App\Jobs;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationConfigurationException;
use App\Integrations\MetaSocial\PublicationData;
use App\Integrations\MetaSocial\PublicationException;
use App\Integrations\MetaSocial\PublicationIdempotencyKey;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Integrations\MetaSocial\Publisher;
use App\Models\Post;
use App\Models\PostSocialPublicationResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PublishPostSocialChannel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public function __construct(
        public readonly int $resultId,
    ) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(Publisher $publisher): void
    {
        $result = PostSocialPublicationResult::query()
            ->with('publication.post')
            ->findOrFail($this->resultId);

        if ($result->status === PostSocialPublicationResult::STATUS_PUBLISHED) {
            return;
        }

        $result->update([
            'status' => PostSocialPublicationResult::STATUS_PROCESSING,
            'attempts' => $result->attempts + 1,
            'error' => null,
        ]);

        try {
            $publication = $result->publication;
            $post = $publication->post;
            $channel = PublicationChannel::from($result->channel);
            $placement = PublicationPlacement::from($result->placement);
            $published = $publisher->publish($channel, new PublicationData(
                caption: $publication->caption,
                hashtags: $publication->hashtags,
                imageUrl: $result->artwork_path ?: $publication->artwork_path,
                postUrl: $this->postUrl($post),
                idempotencyKey: PublicationIdempotencyKey::make(
                    $post->uuid,
                    $publication->uuid,
                    $channel,
                    $placement,
                ),
                placement: $placement,
            ));

            $result->update([
                'status' => PostSocialPublicationResult::STATUS_PUBLISHED,
                'external_id' => $published->externalId,
                'error' => null,
                'published_at' => $published->publishedAt,
            ]);
        } catch (PublicationException $exception) {
            $result->update([
                'status' => $exception->isRetryable()
                    ? PostSocialPublicationResult::STATUS_PENDING
                    : PostSocialPublicationResult::STATUS_FAILED,
                'error' => $exception->getMessage(),
            ]);

            if ($exception->isRetryable()) {
                throw $exception;
            }
        } catch (PublicationConfigurationException $exception) {
            $result->update([
                'status' => PostSocialPublicationResult::STATUS_FAILED,
                'error' => $exception->getMessage(),
            ]);
        } catch (Throwable) {
            $result->update([
                'status' => PostSocialPublicationResult::STATUS_FAILED,
                'error' => 'Unexpected social publication failure.',
            ]);
        }

        if ($result->status === PostSocialPublicationResult::STATUS_PUBLISHED) {
            $this->deletePublishedArtwork($result->artwork_path);
        }
    }

    public function failed(?Throwable $exception): void
    {
        PostSocialPublicationResult::query()
            ->whereKey($this->resultId)
            ->where('status', '!=', PostSocialPublicationResult::STATUS_PUBLISHED)
            ->update([
                'status' => PostSocialPublicationResult::STATUS_FAILED,
                'error' => $exception instanceof PublicationException
                    ? $exception->getMessage()
                    : 'Social publication failed after all retry attempts.',
            ]);
    }

    private function postUrl(Post $post): string
    {
        return match ($post->module) {
            'review' => route('review.read', $post->slug),
            'event' => route('event.read', $post->slug),
            default => route('post.read', $post->slug),
        };
    }

    private function deletePublishedArtwork(?string $artworkUrl): void
    {
        if (blank($artworkUrl)) {
            return;
        }

        $urlPath = parse_url($artworkUrl, PHP_URL_PATH);
        $storagePrefix = '/storage/social-artworks/';

        if (! is_string($urlPath) || ! str_starts_with($urlPath, $storagePrefix)) {
            return;
        }

        Storage::disk('public')->delete(substr($urlPath, strlen('/storage/')));
    }
}
