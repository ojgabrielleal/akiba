<?php

namespace App\Jobs;

use App\Integrations\MetaSocial\Publisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Compatibility adapter for jobs serialized before social publications were consolidated.
 */
class PublishPostSocialChannel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public function __construct(
        public readonly int $resultId,
    ) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(Publisher $publisher): void
    {
        (new PublishSocialPublicationSchedule($this->resultId))->handle($publisher);
    }

    public function failed(?Throwable $exception): void
    {
        (new PublishSocialPublicationSchedule($this->resultId))->failed($exception);
    }
}
