<?php

namespace App\Jobs;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationConfigurationException;
use App\Integrations\MetaSocial\PublicationData;
use App\Integrations\MetaSocial\PublicationException;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Integrations\MetaSocial\Publisher;
use App\Models\SocialPublicationSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PublishSocialPublicationSchedule implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public int $uniqueFor = 3600;

    public function __construct(
        public readonly int $scheduleId,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->scheduleId;
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(Publisher $publisher): void
    {
        $schedule = SocialPublicationSchedule::query()->findOrFail($this->scheduleId);

        if ($schedule->status === SocialPublicationSchedule::STATUS_PUBLISHED) {
            return;
        }

        try {
            $payload = $schedule->payload;
            $published = $publisher->publish(
                PublicationChannel::from($schedule->channel),
                new PublicationData(
                    caption: (string) ($payload['caption'] ?? ''),
                    hashtags: $payload['hashtags'] ?? [],
                    imageUrl: $schedule->media_url,
                    postUrl: (string) ($payload['post_url'] ?? ''),
                    idempotencyKey: $schedule->idempotency_key,
                    placement: PublicationPlacement::from($schedule->placement),
                    linkPreview: (bool) ($payload['link_preview'] ?? false),
                ),
            );

            $schedule->update([
                'status' => SocialPublicationSchedule::STATUS_PUBLISHED,
                'external_id' => $published->externalId,
                'last_error' => null,
                'published_at' => $published->publishedAt,
            ]);

            $this->deleteArtwork($schedule);
        } catch (PublicationException $exception) {
            $schedule->update(['last_error' => $exception->getMessage()]);

            if ($exception->isRetryable()) {
                throw $exception;
            }

            $this->failSchedule($schedule, $exception->getMessage());
        } catch (PublicationConfigurationException $exception) {
            $this->failSchedule($schedule, $exception->getMessage());
        }
    }

    public function failed(?Throwable $exception): void
    {
        $schedule = SocialPublicationSchedule::query()->find($this->scheduleId);

        if (! $schedule || $schedule->status === SocialPublicationSchedule::STATUS_PUBLISHED) {
            return;
        }

        $this->failSchedule(
            $schedule,
            $exception instanceof PublicationException
                ? $exception->getMessage()
                : 'Social publication failed after all retry attempts.',
        );
    }

    private function failSchedule(SocialPublicationSchedule $schedule, string $error): void
    {
        $schedule->update([
            'status' => SocialPublicationSchedule::STATUS_FAILED,
            'last_error' => $error,
        ]);
    }

    private function deleteArtwork(SocialPublicationSchedule $schedule): void
    {
        if (filled($schedule->media_path)) {
            Storage::disk('public')->delete($schedule->media_path);
        }
    }
}
