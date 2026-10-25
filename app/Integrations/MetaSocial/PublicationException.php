<?php

namespace App\Integrations\MetaSocial;

use RuntimeException;
use Throwable;

class PublicationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly PublicationFailure $failure,
        public readonly ?int $httpStatus = null,
        public readonly ?string $providerCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function isRetryable(): bool
    {
        return $this->failure->isRetryable();
    }
}
