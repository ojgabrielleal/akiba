<?php

namespace App\Integrations\MetaSocial;

final class PublicationIdempotencyKey
{
    public static function make(
        string $postUuid,
        string $publicationUuid,
        PublicationChannel $channel,
        PublicationPlacement $placement = PublicationPlacement::Feed,
    ): string {
        return implode(':', [
            'meta',
            $postUuid,
            $publicationUuid,
            $channel->value,
            $placement->value,
        ]);
    }
}
