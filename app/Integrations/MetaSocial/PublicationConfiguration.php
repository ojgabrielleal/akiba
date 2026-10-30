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
            PublicationChannel::Facebook => config('services.meta.facebook_feed_enabled', true)
                ? [PublicationPlacement::Feed]
                : [],
        };
    }

    /**
     * @return list<string>
     */
    public function missingSettings(PublicationChannel $channel): array
    {
        $required = match ($channel) {
            PublicationChannel::Instagram => [
                'graph_version',
                'instagram_access_token',
                'instagram_user_id',
            ],
            PublicationChannel::Facebook => [
                'graph_version',
                'facebook_access_token',
                'facebook_page_id',
            ],
        };

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
