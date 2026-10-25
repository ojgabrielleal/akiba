<?php

namespace App\Integrations\MetaSocial;

use DateTimeImmutable;

final readonly class PublicationResult
{
    public function __construct(
        public PublicationChannel $channel,
        public string $externalId,
        public DateTimeImmutable $publishedAt,
        public ?string $permalink = null,
    ) {}
}
