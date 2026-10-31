<?php

namespace Tests\Unit\Integrations;

use App\Integrations\DiscordWebhookService;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DiscordPostNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance('env', 'production');
        config([
            'services.discord.webhook' => 'https://discord.test/stream',
            'services.discord.post_webhook' => 'https://discord.test/posts',
        ]);
        Http::preventStrayRequests();
        Http::fake(['discord.test/posts' => Http::response([], 204)]);
        Storage::fake('public');
    }

    public function test_it_uploads_the_cover_as_a_jpeg_and_references_it_in_the_embed(): void
    {
        $file = UploadedFile::fake()->image('cover.png', 100, 60);
        Storage::disk('public')->put('images/cover.png', $file->getContent());
        $post = new Post(['title' => 'Nova matéria', 'cover' => '/storage/images/cover.png']);

        $this->assertTrue(app(DiscordWebhookService::class)->sendPostPublishedHook($post, 'https://akiba.test/materia/teste'));

        Http::assertSent(function ($request) {
            $field = collect($request->data())->firstWhere('name', 'payload_json');
            $payload = json_decode($field['contents'], true);

            return $request->url() === 'https://discord.test/posts'
                && $request->hasFile('files[0]')
                && ! isset($payload['username'])
                && ! isset($payload['avatar_url'])
                && str_starts_with($payload['content'], '@here')
                && str_contains($payload['content'], '📰 | **Nova matéria**')
                && str_contains($payload['content'], 'SE LIGA: TEM NOTÍCIA NOVA EM NOSSO SITE!')
                && ! str_contains($payload['content'], '@everyone')
                && $payload['attachments'] === [['id' => 0, 'filename' => 'post.jpg']]
                && $payload['embeds'][0]['image']['url'] === 'attachment://post.jpg'
                && $payload['embeds'][0]['author']['name'] === 'Rede Akiba'
                && $payload['allowed_mentions']['parse'] === ['everyone']
                && str_contains($request->body(), 'image/jpeg')
                && str_contains($request->body(), "\xff\xd8\xff");
        });
        Http::assertSentCount(1);
    }

    public function test_it_sends_json_when_the_post_has_no_image(): void
    {
        $this->assertTrue(app(DiscordWebhookService::class)->sendPostPublishedHook(
            new Post(['title' => 'Sem capa']), 'https://akiba.test/materia/teste',
        ));

        Http::assertSent(fn ($request) => $request->isJson()
            && ! isset($request['embeds'][0]['image'])
            && ! isset($request['attachments']));
        Http::assertSentCount(1);
    }

    public function test_it_falls_back_to_the_image_url_if_the_local_file_is_missing(): void
    {
        $post = new Post(['uuid' => 'test-post', 'title' => 'Capa ausente', 'cover' => '/storage/missing.webp']);
        $this->assertTrue(app(DiscordWebhookService::class)->sendPostPublishedHook($post, 'https://akiba.test/materia/teste'));

        Http::assertSent(fn ($request) => $request->isJson()
            && $request['embeds'][0]['image']['url'] === route('post.social-image', 'test-post'));
        Http::assertSentCount(1);
    }

    public function test_it_prefers_the_generated_discord_artwork_attachment(): void
    {
        $generated = UploadedFile::fake()->image('discord-post.jpg', 1200, 630)->getContent();
        Storage::disk('public')->put('social-artworks/post/publication/discord-post.jpg', $generated);
        $post = new Post(['uuid' => 'test-post', 'title' => 'Arte compartilhada']);

        $this->assertTrue(app(DiscordWebhookService::class)->sendPostPublishedHook(
            $post,
            'https://akiba.test/materia/teste',
            'social-artworks/post/publication/discord-post.jpg',
        ));

        Http::assertSent(function ($request) {
            $field = collect($request->data())->firstWhere('name', 'payload_json');
            $payload = json_decode($field['contents'], true);

            return $request->hasFile('files[0]')
                && $payload['embeds'][0]['image']['url'] === 'attachment://post.jpg'
                && str_contains($request->body(), "\xff\xd8\xff");
        });
        Http::assertSentCount(1);
    }
}
