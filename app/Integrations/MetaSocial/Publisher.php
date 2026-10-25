<?php

namespace App\Integrations\MetaSocial;

interface Publisher
{
    /**
     * @throws PublicationConfigurationException
     * @throws PublicationException
     */
    public function publish(
        PublicationChannel $channel,
        PublicationData $publication,
    ): PublicationResult;
}
