<?php

namespace App\Integrations;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Throwable;

class DiscordWebhookService
{
    public function sendStreamNotificationHook($user, $program): bool
    {
        if (! app()->environment('production')) {
            return false;
        }

        $webhookUrl = config('services.discord.webhook');
        if (! $webhookUrl) {
            return false;
        }

        $payload = [
            'content' => '🎧 @everyone **Bora de Akiba?**',
            'allowed_mentions' => ['parse' => ['everyone']],
            'embeds' => [
                [
                    'title' => "{$program->name} NO AR!",
                    'description' => "**{$user->nickname}** tá ao vivo com o **{$program->name}**!\n\nVem pela música, fica pela companhia. E aquele opening que não sai da sua cabeça? Pede aí que a gente quer ouvir também. 🧡\n\n**[Dá o play e manda seu pedido!](https://akiba.com.br)**",
                    'color' => hexdec('ffaa35'),
                    'author' => [
                        'name' => "DJ {$user->nickname}",
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];

        try {
            return Http::timeout(15)->connectTimeout(5)->post($webhookUrl, $payload)->successful();
        } catch (Throwable $exception) {
            Log::warning('Discord stream notification failed', ['exception' => $exception::class]);

            return false;
        }
    }

    public function sendPostPublishedHook(Post $post, string $url): bool
    {
        if (! app()->environment('production')) {
            return false;
        }

        $webhookUrl = config('services.discord.post_webhook');
        if (! $webhookUrl) {
            return false;
        }

        $description = Str::of(strip_tags($post->content ?? ''))
            ->squish()
            ->limit(180)
            ->toString();

        $attachment = $this->postAttachment($post);
        $image = $attachment
            ? ['url' => 'attachment://post.jpg']
            : $this->postImage($post);

        Log::info('Discord post notification payload prepared', [
            'post_uuid' => $post->uuid,
            'post_title' => $post->title,
            'post_url' => $url,
            'image_url' => $image['url'] ?? null,
        ]);

        $payload = [
            'content' => "📰 **{$post->title}**\n\n{$url}\n\n@everyone, SE LIGA: TEM NOTÍCIA NOVA EM NOSSO SITE!",
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
                    ],
                    'image' => $image,
                    'timestamp' => now()->toIso8601String(),
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

    private function postAttachment(Post $post): ?string
    {
        $image = $post->cover ?: $post->image;

        if (! $image || ! str_starts_with($image, '/storage/')) {
            return null;
        }

        try {
            $contents = Storage::disk('public')->get(substr($image, strlen('/storage/')));

            return (string) (new ImageManager(new Driver()))
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
