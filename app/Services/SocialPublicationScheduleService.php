<?php

namespace App\Services;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationConfiguration;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Jobs\PublishSocialPublicationSchedule;
use App\Models\Onair;
use App\Models\Post;
use App\Models\SocialPublicationSchedule;
use App\Processing\SocialArtworkProcess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SocialPublicationScheduleService
{
    public function __construct(
        private PublicationConfiguration $configuration,
        private SocialArtworkDataService $artworkData,
        private SocialArtworkProcess $artwork,
    ) {}

    public function schedulePost(Post $post): Collection
    {
        if ($post->status !== 'published') {
            return collect();
        }

        return $this->schedule(
            publishable: $post,
            data: $this->artworkData->map($post),
            caption: $this->postCaption($post),
            hashtags: $post->social['hashtags'] ?? $post->tags()->limit(30)->pluck('name')->all(),
            postUrl: $this->postUrl($post),
            artworkOwner: $post->uuid,
            templatePrefix: 'instagram',
            fallbackUrl: blank($post->cover ?: $post->image)
                ? null
                : route('post.social-image', $post->uuid),
        );
    }

    public function scheduleLive(Onair $onair, array $social): Collection
    {
        return $this->schedule(
            publishable: $onair,
            data: $this->artworkData->mapLive($onair),
            caption: $this->liveCaption($social['text']),
            hashtags: $social['hashtags'] ?? [],
            postUrl: url('/'),
            artworkOwner: 'live-'.$onair->uuid,
            templatePrefix: 'instagram-live',
        );
    }

    private function schedule(
        Model $publishable,
        array $data,
        string $caption,
        array $hashtags,
        string $postUrl,
        string $artworkOwner,
        string $templatePrefix,
        bool $facebookLinkPreview = false,
        ?string $fallbackUrl = null,
    ): Collection {
        $targets = collect(PublicationChannel::cases())
            ->flatMap(fn (PublicationChannel $channel) => array_map(
                fn (PublicationPlacement $placement): array => [$channel, $placement],
                $this->configuration->enabledPlacements($channel),
            ))
            ->values();

        if ($targets->isEmpty()) {
            return collect();
        }

        $publicationUuid = (string) Str::uuid();
        $artworks = [];

        try {
            foreach ($targets as [$channel, $placement]) {
                if ($channel === PublicationChannel::Facebook && $facebookLinkPreview) {
                    $artworks[$channel->value][$placement->value] = (object) [
                        'path' => null,
                        'url' => $postUrl,
                    ];

                    continue;
                }

                $artworks[$channel->value][$placement->value] = $this->artwork->generate(
                    $this->template($templatePrefix, $channel, $placement),
                    $data,
                    $artworkOwner,
                    $publicationUuid,
                );
            }
        } catch (Throwable $exception) {
            $this->deleteArtworks($artworks);

            Log::warning('Social artwork generation failed.', [
                'publishable_type' => $publishable->getMorphClass(),
                'publishable_uuid' => $publishable->uuid,
                'exception' => $exception::class,
            ]);

            if (blank($fallbackUrl)) {
                return collect();
            }

            $artworks = $targets->reduce(function (array $carry, array $target) use ($fallbackUrl): array {
                [$channel, $placement] = $target;
                $carry[$channel->value][$placement->value] = (object) [
                    'path' => null,
                    'url' => $fallbackUrl,
                ];

                return $carry;
            }, []);
        }

        try {
            $schedules = DB::transaction(function () use (
                $publishable,
                $targets,
                $artworks,
                $publicationUuid,
                $caption,
                $hashtags,
                $postUrl,
                $facebookLinkPreview,
            ): Collection {
                return $targets->map(function (array $target) use (
                    $publishable,
                    $artworks,
                    $publicationUuid,
                    $caption,
                    $hashtags,
                    $postUrl,
                    $facebookLinkPreview,
                ): SocialPublicationSchedule {
                    [$channel, $placement] = $target;
                    $artwork = $artworks[$channel->value][$placement->value];

                    return $publishable->socialPublicationSchedules()->create([
                        'channel' => $channel->value,
                        'placement' => $placement->value,
                        'payload' => [
                            'caption' => $caption,
                            'hashtags' => array_values($hashtags),
                            'post_url' => $postUrl,
                            'link_preview' => $channel === PublicationChannel::Facebook && $facebookLinkPreview,
                        ],
                        'media_path' => $artwork->path,
                        'media_url' => $artwork->url,
                        'status' => SocialPublicationSchedule::STATUS_PENDING,
                        'idempotency_key' => implode(':', [
                            'social',
                            class_basename($publishable),
                            $publishable->uuid,
                            $publicationUuid,
                            $channel->value,
                            $placement->value,
                        ]),
                        'scheduled_at' => now(),
                    ]);
                });
            });
        } catch (Throwable $exception) {
            $this->deleteArtworks($artworks);

            throw $exception;
        }

        $schedules->each(
            fn (SocialPublicationSchedule $schedule) => PublishSocialPublicationSchedule::dispatch($schedule->id),
        );

        return $schedules;
    }

    private function template(
        string $prefix,
        PublicationChannel $channel,
        PublicationPlacement $placement,
    ): string {
        $suffix = str_starts_with($prefix, 'instagram')
            ? substr($prefix, strlen('instagram'))
            : '';

        return $channel->value.$suffix.'-'.$placement->value;
    }

    private function postCaption(Post $post): string
    {
        return implode("\n\n", [
            $post->social['text'] ?? $post->title,
            "🔗 Saiba mais:\n".$this->shortPostUrl($post),
            "💬 Venha para a comunidade da Rede Akiba no Discord e converse com gente que te entende:\nhttps://discord.akiba.com.br",
        ]);
    }

    private function liveCaption(string $text): string
    {
        return implode("\n\n", [
            $text,
            "🎧 Ouça agora:\nhttps://akiba.com.br",
            "💬 Venha para a comunidade da Rede Akiba no Discord e converse com gente que te entende:\nhttps://discord.akiba.com.br",
        ]);
    }

    private function postUrl(Post $post): string
    {
        return match ($post->module) {
            'review' => route('review.read', $post->slug),
            'event' => route('event.read', $post->slug),
            default => route('post.read', $post->slug),
        };
    }

    private function shortPostUrl(Post $post): string
    {
        return filled($post->short_code)
            ? route('post.short', $post->short_code)
            : $this->postUrl($post);
    }

    private function deleteArtworks(array $artworks): void
    {
        foreach ($artworks as $channelArtworks) {
            foreach ($channelArtworks as $artwork) {
                if (filled($artwork->path)) {
                    Storage::disk('public')->delete($artwork->path);
                }
            }
        }
    }
}
