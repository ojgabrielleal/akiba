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
            (object) ['nickname' => 'Yagami', 'gender' => 'male', 'avatar' => 'https://cdn.example.com/avatar.png'],
            (object) ['name' => 'Conexão Akiba'],
        ));

        Http::assertSent(function ($request) {
            $payload = $request->data();
            $embed = $payload['embeds'][0];

            return $request->isJson()
                && $payload['allowed_mentions']['parse'] === ['everyone']
                && str_contains($payload['content'], '@everyone')
                && ! isset($payload['attachments'])
                && ! isset($embed['image'])
                && ! isset($embed['thumbnail'])
                && ! isset($embed['author']['icon_url'])
                && $embed['title'] === 'Conexão Akiba NO AR!'
                && str_contains($embed['description'], 'Yagami')
                && str_contains($embed['description'], 'https://akiba.com.br');
        });
        Http::assertSentCount(1);
    }
}
