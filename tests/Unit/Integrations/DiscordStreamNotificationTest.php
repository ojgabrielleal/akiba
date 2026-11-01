<?php

namespace Tests\Unit\Integrations;

use App\Integrations\DiscordWebhookService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscordStreamNotificationTest extends TestCase
{
    public function test_discord_switch_disables_discord_notifications(): void
    {
        $this->app->instance('env', 'production');
        config([
            'services.discord.publication_enabled' => false,
            'services.discord.webhook' => 'https://discord.test/webhook',
        ]);
        Http::fake();

        $this->assertFalse(app(DiscordWebhookService::class)->sendStreamNotificationHook(
            (object) ['nickname' => 'Yagami'],
            (object) ['name' => 'Conexão Akiba'],
        ));
        Http::assertNothingSent();
    }

    public function test_it_sends_the_current_text_embed_without_an_image_attachment(): void
    {
        $this->app->instance('env', 'production');
        config(['services.discord.webhook' => 'https://discord.test/webhook']);
        Http::preventStrayRequests();
        Http::fake(['discord.test/*' => Http::response([], 204)]);

        $this->assertTrue(app(DiscordWebhookService::class)->sendStreamNotificationHook(
            (object) ['nickname' => 'Yagami', 'avatar' => '/storage/users/yagami.webp'],
            (object) ['name' => 'Conexão Akiba'],
        ));

        Http::assertSent(function ($request) {
            $payload = $request->data();
            $embed = $payload['embeds'][0];

            return $request->isJson()
                && $payload['allowed_mentions']['parse'] === ['everyone']
                && ! isset($payload['username'])
                && ! isset($payload['avatar_url'])
                && $payload['content'] === '🎧 @here **Bora de Akiba? DJ Yagami está ao vivo**'
                && str_contains($payload['content'], '@here')
                && ! str_contains($payload['content'], '@everyone')
                && ! isset($payload['attachments'])
                && ! isset($embed['image'])
                && ! isset($embed['author'])
                && $embed['title'] === '🔴 Conexão Akiba ESTÁ NO AR!'
                && $embed['fields'][0] === ['name' => '🎙️ LOCUTOR', 'value' => "Yagami\n\u{200B}", 'inline' => true]
                && $embed['fields'][1] === ['name' => '📡 PROGRAMA', 'value' => "Conexão Akiba\n\u{200B}", 'inline' => true]
                && str_contains($embed['fields'][2]['value'], 'https://akiba.com.br')
                && $embed['thumbnail']['url'] === 'https://akiba.com.br/storage/users/yagami.webp'
                && ! isset($embed['footer'])
                && ! isset($embed['timestamp']);
        });
        Http::assertSentCount(1);
    }

}
