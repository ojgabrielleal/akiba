<?php

namespace App\Integrations;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class DiscordWebhookService
{
    public function postNotificationsEnabled(): bool
    {
        return $this->discordPublicationEnabled()
            && app()->environment('production')
            && filled(config('services.discord.post_webhook'));
    }

    public function streamNotificationsEnabled(): bool
    {
        return $this->discordPublicationEnabled()
            && app()->environment('production')
            && filled(config('services.discord.webhook'));
    }

    public function sendStreamNotificationHook($user, $program): bool
    {
        if (! $this->streamNotificationsEnabled()) {
            return false;
        }

        $webhookUrl = config('services.discord.webhook');
        $siteUrl = rtrim((string) config('app.url'), '/');

        if (in_array(parse_url($siteUrl, PHP_URL_HOST), ['localhost', '127.0.0.1'], true)) {
            $siteUrl = 'https://akiba.com.br';
        }

        $hostAvatarUrl = $this->publicImageUrl($user->avatar ?? null, $siteUrl);

        $payload = [
            'content' => "🎧 @here **Bora de Akiba? DJ {$user->nickname} está ao vivo**",
            'allowed_mentions' => ['parse' => ['everyone']],
            'embeds' => [
                array_filter([
                    'title' => "🔴 {$program->name} ESTÁ NO AR!",
                    'url' => $siteUrl,
                    'description' => "A programação ao vivo começou. Vem curtir música, companhia e pedir aquele opening que não sai da cabeça. 🧡\n\u{200B}",
                    'color' => hexdec('ffaa35'),
                    'fields' => [
                        [
                            'name' => '🎙️ LOCUTOR',
                            'value' => "{$user->nickname}\n\u{200B}",
                            'inline' => true,
                        ],
                        [
                            'name' => '📡 PROGRAMA',
                            'value' => "{$program->name}\n\u{200B}",
                            'inline' => true,
                        ],
                        [
                            'name' => '🎧 BORA DE AKIBA?',
                            'value' => "[OUVIR AO VIVO E PEDIR MÚSICA]({$siteUrl})",
                            'inline' => false,
                        ],
                    ],
                    'thumbnail' => $hostAvatarUrl ? ['url' => $hostAvatarUrl] : null,
                ]),
            ],
        ];

        try {
            return Http::timeout(30)->connectTimeout(5)->post($webhookUrl, $payload)->successful();
        } catch (Throwable $exception) {
            Log::warning('Discord stream notification failed', ['exception' => $exception::class]);

            return false;
        }
    }

    public function sendPostPublishedHook(Post $post, string $url, ?string $artworkPath = null): bool
    {
        if (! $this->postNotificationsEnabled()) {
            return false;
        }

        $webhookUrl = config('services.discord.post_webhook');
        $description = Str::of(strip_tags($post->content ?? ''))
            ->squish()
            ->limit(180)
            ->toString();

        $attachment = $this->postAttachment($post, $artworkPath);
        $image = $attachment
            ? ['url' => 'attachment://post.jpg']
            : $this->postImage($post);
        $brandIconUrl = url('/img/pwa/icon-256.png');

        Log::info('Discord post notification payload prepared', [
            'post_uuid' => $post->uuid,
            'post_title' => $post->title,
            'post_url' => $url,
            'image_url' => $image['url'] ?? null,
        ]);

        $payload = [
            'content' => "📰 | **{$post->title}**\n\n{$url}\n\n@here SE LIGA: TEM NOTÍCIA NOVA EM NOSSO SITE!",
            'allowed_mentions' => [
                'parse' => ['everyone'],
            ],
            'embeds' => [
                array_filter([
                    'title' => $post->title,
                    'url' => $url,
                    'description' => $description ?: null,
                    'color' => hexdec('ffaa35'),
                    'author' => [
                        'name' => 'Rede Akiba',
                        'url' => config('app.url'),
                        'icon_url' => $brandIconUrl,
                    ],
                    'image' => $image,
                ]),
            ],
        ];

        try {
            $request = Http::timeout(30)->connectTimeout(5);

            if ($attachment !== null) {
                $payload['attachments'] = [['id' => 0, 'filename' => 'post.jpg']];

                return $request
                    ->attach('files[0]', $attachment, 'post.jpg', ['Content-Type' => 'image/jpeg'])
                    ->post($webhookUrl, ['payload_json' => json_encode($payload, JSON_THROW_ON_ERROR)])
                    ->successful();
            }

            return $request->post($webhookUrl, $payload)->successful();
        } catch (Throwable $exception) {
            Log::warning('Discord post notification failed', ['exception' => $exception::class]);

            return false;
        }
    }

    private function discordPublicationEnabled(): bool
    {
        return (bool) config('services.discord.publication_enabled', true);
    }

    private function publicImageUrl(?string $image, string $siteUrl): ?string
    {
        if (blank($image)) {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return $siteUrl.'/'.ltrim($image, '/');
    }

    private function postAttachment(Post $post, ?string $artworkPath): ?string
    {
        if ($artworkPath !== null) {
            try {
                return Storage::disk('public')->get($artworkPath);
            } catch (Throwable $exception) {
                Log::warning('Discord generated post artwork could not be loaded.', [
                    'post_uuid' => $post->uuid,
                    'artwork_path' => $artworkPath,
                    'exception' => $exception::class,
                ]);
            }
        }

        $image = $post->cover ?: $post->image;

        if (! $image || ! str_starts_with($image, '/storage/')) {
            return null;
        }

        try {
            $contents = Storage::disk('public')->get(substr($image, strlen('/storage/')));

            return (string) (new ImageManager(new Driver))
                ->read($contents)
                ->scaleDown(width: 1600, height: 1600)
                ->toJpeg(85);
        } catch (Throwable $exception) {
            Log::warning('Discord post attachment preparation failed', [
                'post_uuid' => $post->uuid,
                'exception' => $exception::class,
            ]);

            return null;
        }
    }

    private function postImage(Post $post): ?array
    {
        if (! ($post->cover ?: $post->image)) {
            return null;
        }

        return ['url' => route('post.social-image', $post->uuid)];
    }
}
