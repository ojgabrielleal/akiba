<?php

namespace Tests\Feature\Views;

use Tests\TestCase;

class SocialArtworkViewTest extends TestCase
{
    public function test_it_renders_each_social_artwork_with_fixed_dimensions(): void
    {
        $html = view('social.artwork')->render();

        $this->assertStringContainsString('data-social-template="instagram-feed"', $html);
        $this->assertStringContainsString('data-social-template="instagram-live-feed"', $html);
        $this->assertStringContainsString('data-social-template="facebook-feed"', $html);
        $this->assertStringContainsString('data-social-template="facebook-live-feed"', $html);
        $this->assertStringContainsString('data-width="1080"', $html);
        $this->assertStringContainsString('data-height="1350"', $html);
        $this->assertStringContainsString('data-social-template="discord-post"', $html);
        $this->assertStringContainsString('data-width="1200"', $html);
        $this->assertStringContainsString('data-height="630"', $html);
        $this->assertStringContainsString('DJ Yagami', $html);
        $this->assertStringContainsString('class="live-artwork__now">Agora', $html);
        $this->assertStringContainsString('class="live-artwork__status">No ar', $html);
        $this->assertStringNotContainsString('class="live-artwork__program-logo"', $html);
        $this->assertStringNotContainsString('Transmissão Ao vivo', $html);
        $this->assertSame(5, substr_count($html, 'class="social-artwork"'));
        $this->assertStringContainsString('class="social-artwork__avatar"', $html);
        $this->assertStringNotContainsString('<span>akiba.com.br</span>', $html);
        $this->assertStringNotContainsString('class="social-artwork__channel"', $html);
        $this->assertSame(5, substr_count($html, 'class="social-artwork__jp-stamp"'));
        $this->assertStringContainsString('アニメ · マンガ · 音楽 · 日本', $html);
        $this->assertStringNotContainsString('social-artwork__manga-lines', $html);
        $this->assertStringNotContainsString('social-artwork__anime-mark', $html);
        $this->assertStringNotContainsString('social-artwork__panel-corner', $html);
    }

    public function test_it_escapes_dynamic_content(): void
    {
        $html = view('social.artwork', [
            'artwork' => [
                'title' => '<script>alert("akiba")</script>',
            ],
        ])->render();

        $this->assertStringNotContainsString('<script>alert("akiba")</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;akiba&quot;)&lt;/script&gt;', $html);
    }
}
