<?php

namespace Tests\Feature\Integrations;

use App\Integrations\AnimeNewsFeedService;
use App\Integrations\NewsArticleImageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsArticleImageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('public');
        config(['services.news_article_images.anime-news-network.interval' => 0]);
        Http::preventStrayRequests();
    }

    public function test_open_graph_is_preferred_and_cached(): void
    {
        Http::fake(['*' => Http::response('<meta name="twitter:image" content="/twitter.jpg"><meta content="/cover.jpg" property="og:image">', 200, ['Content-Type' => 'text/html'])]);
        $service = app(NewsArticleImageService::class);
        $url = 'https://www.animenewsnetwork.com/news/example';
        $this->assertSame('https://www.animenewsnetwork.com/cover.jpg', $service->resolve('anime-news-network', $url));
        $this->assertSame('https://www.animenewsnetwork.com/cover.jpg', $service->resolve('anime-news-network', $url));
        Http::assertSentCount(1);
    }

    public function test_twitter_and_article_images_are_fallbacks(): void
    {
        Http::fake([
            '*/news/1' => Http::response('<meta name="twitter:image" content="//www.animenewsnetwork.com/cover.jpg">', 200, ['Content-Type' => 'text/html']),
            '*/news/2' => Http::response('<img src="/logo.jpg"><article><img src="javascript:bad"><img src="/photo.jpg"></article>', 200, ['Content-Type' => 'text/html']),
        ]);
        $service = app(NewsArticleImageService::class);
        $this->assertSame('https://www.animenewsnetwork.com/cover.jpg', $service->resolve('anime-news-network', 'https://www.animenewsnetwork.com/news/1'));
        $this->assertSame('https://www.animenewsnetwork.com/photo.jpg', $service->resolve('anime-news-network', 'https://www.animenewsnetwork.com/news/2'));
    }

    public function test_failure_is_cached_and_does_not_throw(): void
    {
        Http::fake(['*' => Http::response('Forbidden', 403)]);
        $service = app(NewsArticleImageService::class);
        $this->assertNull($service->resolve('anime-news-network', 'https://www.animenewsnetwork.com/news/1'));
        $this->assertNull($service->resolve('anime-news-network', 'https://www.animenewsnetwork.com/news/1'));
        Http::assertSentCount(1);
    }

    public function test_unknown_hosts_and_cross_host_redirects_are_not_fetched(): void
    {
        $service = app(NewsArticleImageService::class);
        $this->assertNull($service->resolve('anime-news-network', 'https://localhost/news/1'));
        $this->assertNull($service->resolve('anime-news-network', 'https://www.animenewsnetwork.com/my'));
        Http::assertNothingSent();
        Http::fake(['www.animenewsnetwork.com/*' => Http::response('', 302, ['Location' => 'http://127.0.0.1/private'])]);
        $this->assertNull($service->resolve('anime-news-network', 'https://www.animenewsnetwork.com/news/1'));
        Http::assertSentCount(1);
    }

    public function test_same_host_redirect_is_followed(): void
    {
        Http::fake([
            '*/news/1' => Http::response('', 301, ['Location' => '/news/2']),
            '*/news/2' => Http::response('<meta property="og:image" content="/cover.jpg">', 200, ['Content-Type' => 'text/html']),
        ]);
        $this->assertSame('https://www.animenewsnetwork.com/cover.jpg', app(NewsArticleImageService::class)->resolve('anime-news-network', 'https://www.animenewsnetwork.com/news/1'));
        Http::assertSentCount(2);
    }

    public function test_only_visible_items_without_images_are_enriched(): void
    {
        $items = '';
        for ($i = 1; $i <= 5; $i++) {
            $items .= '<item><title>News '.$i.'</title><link>https://www.animenewsnetwork.com/news/'.$i.'</link><guid>'.$i.'</guid>';
            $items .= $i === 3 ? '<enclosure url="https://example.com/existing.jpg"/>' : '';
            $items .= '</item>';
        }
        Http::fake([
            '*/news/rss.xml' => Http::response('<rss><channel>'.$items.'</channel></rss>'),
            '*/cover.jpg' => Http::response($this->png(), 200, ['Content-Type' => 'image/png']),
            '*/news/4' => Http::response('<meta property="og:image" content="/cover.jpg">', 200, ['Content-Type' => 'text/html']),
        ]);
        $page = app(AnimeNewsFeedService::class)->paginate('anime-news-network', 2, 2);
        $this->assertSame(5, $page->total());
        $this->assertSame('https://example.com/existing.jpg', $page->items()[0]['image']);
        $this->assertStringStartsWith('/storage/news-feed/', $page->items()[1]['image']);
        Storage::disk('public')->assertExists(substr($page->items()[1]['image'], strlen('/storage/')));
        Http::assertSentCount(3);
    }
    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jN1sAAAAASUVORK5CYII=');
    }

    public function test_preview_stores_real_image_bytes_and_reuses_local_copy(): void
    {
        Http::fake(['*' => Http::response($this->png(), 200, ['Content-Type' => 'image/png'])]);
        $service = app(NewsArticleImageService::class);
        $url = 'https://www.animenewsnetwork.com/cover.jpg';
        $preview = $service->preview('anime-news-network', $url);
        $this->assertStringStartsWith('/storage/news-feed/', $preview);
        $this->assertSame($this->png(), Storage::disk('public')->get(substr($preview, 9)));
        $this->assertSame($preview, $service->preview('anime-news-network', $url));
        Http::assertSentCount(1);
    }

    public function test_preview_rejects_html_and_unknown_hosts(): void
    {
        Http::fake(['*' => Http::response('<html>Unavailable</html>', 200, ['Content-Type' => 'image/jpeg'])]);
        $service = app(NewsArticleImageService::class);
        $this->assertNull($service->preview('anime-news-network', 'https://localhost/cover.jpg'));
        Http::assertNothingSent();
        $this->assertNull($service->preview('anime-news-network', 'https://www.animenewsnetwork.com/cover.jpg'));
        $this->assertNull($service->preview('anime-news-network', 'https://www.animenewsnetwork.com/cover.jpg'));
        $this->assertSame([], Storage::disk('public')->allFiles());
        Http::assertSentCount(1);
    }

}
