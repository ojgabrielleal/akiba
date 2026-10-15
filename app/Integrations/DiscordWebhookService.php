<?php

namespace App\Integrations;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
            'content' => '🎧 **Bora de Akiba?**',
            'allowed_mentions' => ['parse' => []],
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

        $image = $this->postImage($post);

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
            return Http::post($webhookUrl, $payload)->successful();
        } catch (Throwable) {
            return false;
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
