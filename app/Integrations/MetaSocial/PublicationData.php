<?php

namespace App\Integrations\MetaSocial;

final readonly class PublicationData
{
    /**
     * @param  list<string>  $hashtags
     */
    public function __construct(
        public string $caption,
        public array $hashtags,
        public string $imageUrl,
        public string $postUrl,
        public string $idempotencyKey,
        public PublicationPlacement $placement = PublicationPlacement::Feed,
        public bool $linkPreview = false,
    ) {}
}
