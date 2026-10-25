<?php

namespace App\Services;

use App\Integrations\MetaSocial\PublicationChannel;
use App\Integrations\MetaSocial\PublicationConfiguration;
use App\Integrations\MetaSocial\PublicationPlacement;
use App\Jobs\PublishPostSocialChannel;
use App\Models\Post;
use App\Models\PostSocialPublication;
use App\Models\PostSocialPublicationResult;
use App\Processing\SocialArtworkProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PostSocialPublicationService
{
    public function __construct(
        private PublicationConfiguration $configuration,
        private SocialArtworkDataService $artworkData,
        private SocialArtworkProcess $artwork,
    ) {}

    public function prepare(Post $post): ?PostSocialPublication
    {
        if ($post->status !== 'published') {
            return null;
        }

        $targets = collect(PublicationChannel::cases())
            ->flatMap(fn (PublicationChannel $channel) => array_map(
                fn (PublicationPlacement $placement): array => [$channel, $placement],
                $this->configuration->enabledPlacements($channel),
            ))
            ->values();

        if ($targets->isEmpty()) {
            return null;
        }

        $publicationUuid = (string) Str::uuid();
        $data = $this->artworkData->map($post);
        $artworks = [];

        try {
            foreach ($targets as [$channel, $placement]) {
                $template = $this->template($channel, $placement);
                $artworks[$channel->value][$placement->value] = $this->artwork->generate(
                    $template,
                    $data,
                    $post->uuid,
                    $publicationUuid,
                );
            }
        } catch (Throwable $exception) {
            $this->deleteArtworks($artworks);

            Log::warning('Social artwork generation failed.', [
                'post_uuid' => $post->uuid,
                'exception' => $exception::class,
            ]);

            if (blank($post->cover ?: $post->image)) {
                return null;
            }

            $fallbackUrl = route('post.social-image', $post->uuid);
            $artworks = [];

            foreach ($targets as [$channel, $placement]) {
                $artworks[$channel->value][$placement->value] = (object) [
                    'path' => null,
                    'url' => $fallbackUrl,
                ];
            }
        }

        try {
            $publication = DB::transaction(function () use ($post, $targets, $artworks, $publicationUuid) {
                $primaryArtwork = collect($artworks)->flatten(1)->first();
                $publication = $post->socialPublications()->create([
                    'uuid' => $publicationUuid,
                    'caption' => $this->caption($post),
                    'hashtags' => $post->social['hashtags'] ?? $post->tags()->pluck('name')->all(),
                    'artwork_path' => $primaryArtwork->url,
                    'requested_at' => now(),
                ]);

                $publication->results()->createMany($targets
                    ->map(fn (array $target): array => [
                        'channel' => $target[0]->value,
                        'placement' => $target[1]->value,
                        'artwork_path' => $artworks[$target[0]->value][$target[1]->value]->url,
                        'status' => PostSocialPublicationResult::STATUS_PENDING,
                    ])
                    ->all());

                return $publication->load('results');
            });
        } catch (Throwable $exception) {
            $this->deleteArtworks($artworks);

            throw $exception;
        }

        $publication->results->each(
            fn (PostSocialPublicationResult $result) => PublishPostSocialChannel::dispatch($result->id),
        );

        return $publication;
    }

    private function caption(Post $post): string
    {
        $text = $post->social['text'] ?? $post->title;
        $url = match ($post->module) {
            'review' => route('review.read', $post->slug),
            'event' => route('event.read', $post->slug),
            default => route('post.read', $post->slug),
        };

        return implode("\n\n", [
            $text,
            "Leia em: {$url}",
            'Venha para a nossa comunidade do Discord, converse com gente que te entende.',
            'Entre em: https://discord.akiba.com.br',
        ]);
    }

    private function template(
        PublicationChannel $channel,
        PublicationPlacement $placement,
    ): string {
        return match ([$channel, $placement]) {
            [PublicationChannel::Instagram, PublicationPlacement::Feed] => 'instagram-feed',
            [PublicationChannel::Instagram, PublicationPlacement::Story] => 'instagram-story',
        };
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
