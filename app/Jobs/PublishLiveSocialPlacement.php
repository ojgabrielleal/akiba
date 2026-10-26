<?php

namespace App\Jobs;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationData;
use App\Integrations\MetaSocial\PublicationException;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Integrations\MetaSocial\Publisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Compatibility adapter for live jobs serialized before social publications were consolidated.
 */
class PublishLiveSocialPlacement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public function __construct(
        public readonly string $onairUuid,
        public readonly string $publicationUuid,
        public readonly PublicationPlacement $placement,
        public readonly string $caption,
        public readonly array $hashtags,
        public readonly string $imageUrl,
        public readonly string $imagePath,
    ) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(Publisher $publisher): void
    {
        try {
            $publisher->publish(PublicationChannel::Instagram, new PublicationData(
                caption: $this->caption,
                hashtags: $this->hashtags,
                imageUrl: $this->imageUrl,
                postUrl: url('/'),
                idempotencyKey: implode(':', ['meta', 'live', $this->onairUuid, $this->publicationUuid, $this->placement->value]),
                placement: $this->placement,
            ));

            Storage::disk('public')->delete($this->imagePath);
        } catch (PublicationException $exception) {
            if ($exception->isRetryable()) {
                throw $exception;
            }

            Log::warning('Legacy live social publication failed.', [
                'onair_uuid' => $this->onairUuid,
                'placement' => $this->placement->value,
                'error' => $exception->getMessage(),
            ]);
            Storage::disk('public')->delete($this->imagePath);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Storage::disk('public')->delete($this->imagePath);
    }
}
