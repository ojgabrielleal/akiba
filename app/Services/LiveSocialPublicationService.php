<?php

namespace App\Services;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationConfiguration;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Jobs\PublishLiveSocialPlacement;
use App\Models\Onair;
use App\Processing\SocialArtworkProcess;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LiveSocialPublicationService
{
    public function __construct(
        private PublicationConfiguration $configuration,
        private SocialArtworkDataService $artworkData,
        private SocialArtworkProcess $artwork,
    ) {}

    public function prepare(Onair $onair, array $social): void
    {
        $publicationUuid = (string) Str::uuid();

        foreach ($this->configuration->enabledPlacements(PublicationChannel::Instagram) as $placement) {
            try {
                $template = $placement === PublicationPlacement::Story
                    ? 'instagram-live-story'
                    : 'instagram-live-feed';
                $artwork = $this->artwork->generate(
                    $template,
                    $this->artworkData->mapLive($onair),
                    'live-'.$onair->uuid,
                    $publicationUuid,
                );

                PublishLiveSocialPlacement::dispatch(
                    $onair->uuid,
                    $publicationUuid,
                    $placement,
                    $this->caption($social['text']),
                    $social['hashtags'] ?? [],
                    $artwork->url,
                    $artwork->path,
                );
            } catch (Throwable $exception) {
                Log::warning('Live social artwork generation failed.', [
                    'onair_uuid' => $onair->uuid,
                    'placement' => $placement->value,
                    'exception' => $exception::class,
                ]);
            }
        }
    }

    private function caption(string $text): string
    {
        return implode("\n\n", [
            $text,
            'Ouça em: https://akiba.com.br',
            'Venha para a nossa comunidade do Discord, converse com gente que te entende.',
            'Entre em: https://discord.akiba.com.br',
        ]);
    }
}
