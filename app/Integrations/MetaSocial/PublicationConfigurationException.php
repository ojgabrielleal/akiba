<?php

namespace App\Integrations\MetaSocial;

use RuntimeException;

class PublicationConfigurationException extends RuntimeException
{
    public static function disabled(PublicationChannel $channel): self
    {
        return new self("Meta publication channel [{$channel->value}] is disabled.");
    }

    /**
     * @param  list<string>  $settings
     */
    public static function missing(PublicationChannel $channel, array $settings): self
    {
        return new self(sprintf(
            'Meta publication channel [%s] is missing configuration: %s.',
            $channel->value,
            implode(', ', $settings),
        ));
    }
}
