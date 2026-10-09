<?php

namespace App\Integrations;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class DiscordWebhookService
{
    public function sendStreamNotificationHook($user, $program)
    {
        if (!app()->environment('production')) return false;

        $webhookUrl = config('services.discord.webhook');
        if (!$webhookUrl) return false;

        $genderTitle = $user->gender === 'male' ? 'O DJ' : 'A DJ';
        $payload = [
            'content' => "@everyone Olá otakus queridos!\n\n🔴 **PROGRAMA AO VIVO rolando agora!**",
            'embeds' => [
                [
                    'title' => "{$program->name} NO AR!",
                    'description' => "🎧 {$genderTitle} **{$user->nickname}** sentou no estúdio e já está no ar!\n\nOuça agora na Akiba.\n👉 **[Clique aqui para sintonizar na Akiba!](https://akiba.com.br)**",
                    'color' => hexdec("ffaa35"),
                    'author' => [
                        'name' => "DJ {$user->nickname}",
                    ],
                    'timestamp' => now()->toIso8601String()
                ]
            ]
        ];

        Http::post($webhookUrl, $payload);
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
                    'image' => $this->postImage($post),
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
