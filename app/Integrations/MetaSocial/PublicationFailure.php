<?php

namespace App\Integrations\MetaSocial;

enum PublicationFailure: string
{
    case Authentication = 'authentication';
    case Permission = 'permission';
    case Validation = 'validation';
    case RateLimit = 'rate_limit';
    case Temporary = 'temporary';
    case Unknown = 'unknown';

    public function isRetryable(): bool
    {
        return match ($this) {
            self::RateLimit, self::Temporary => true,
            default => false,
        };
    }
}
