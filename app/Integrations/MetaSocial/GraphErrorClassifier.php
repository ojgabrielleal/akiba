<?php

namespace App\Integrations\MetaSocial;

final class GraphErrorClassifier
{
    /**
     * @param  array<string, mixed>  $error
     */
    public static function classify(array $error): PublicationFailure
    {
        if (($error['is_transient'] ?? false) === true) {
            return PublicationFailure::Temporary;
        }

        return match ((int) ($error['code'] ?? 0)) {
            190 => PublicationFailure::Authentication,
            10, 200 => PublicationFailure::Permission,
            100 => PublicationFailure::Validation,
            4, 17, 32, 613 => PublicationFailure::RateLimit,
            1, 2 => PublicationFailure::Temporary,
            default => PublicationFailure::Unknown,
        };
    }
}
