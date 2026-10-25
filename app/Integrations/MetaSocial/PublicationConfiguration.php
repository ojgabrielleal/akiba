<?php

namespace App\Integrations\MetaSocial;

final class PublicationConfiguration
{
    public function isEnabled(PublicationChannel $channel): bool
    {
        return (bool) config("services.meta.{$channel->value}_enabled", false);
    }

    public function isReady(PublicationChannel $channel): bool
    {
        return $this->isEnabled($channel) && $this->missingSettings($channel) === [];
    }

    /**
     * @return list<PublicationPlacement>
     */
    public function enabledPlacements(PublicationChannel $channel): array
    {
        if (! $this->isEnabled($channel)) {
            return [];
        }

        return match ($channel) {
            PublicationChannel::Instagram => config('services.meta.instagram_feed_enabled', true)
                ? [PublicationPlacement::Feed]
                : [],
        };
    }

    /**
     * @return list<string>
     */
    public function missingSettings(PublicationChannel $channel): array
    {
        $required = [
            'graph_version',
            'access_token',
            match ($channel) {
                PublicationChannel::Instagram => 'instagram_user_id',
            },
        ];

        return array_values(array_filter(
            $required,
            fn (string $setting): bool => blank(config("services.meta.{$setting}")),
        ));
    }

    public function ensureReady(PublicationChannel $channel): void
    {
        if (! $this->isEnabled($channel)) {
            throw PublicationConfigurationException::disabled($channel);
        }

        $missingSettings = $this->missingSettings($channel);

        if ($missingSettings !== []) {
            throw PublicationConfigurationException::missing($channel, $missingSettings);
        }
    }
}
