<?php

namespace Tests\Feature\Integrations;

use App\Integrations\AnimeNewsFeedService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AnimeNewsFeedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Exercise the generic Atom reader without registering a production source.
        config(['services.anime_news_sources' => [
            ...config('services.anime_news_sources'),
            ['name' => 'Sample Atom', 'slug' => 'sample-atom', 'base_url' => 'https://example.net',
                'type' => 'rss', 'feed_url' => 'https://example.net/comic/feed/news',
                'language' => 'ja', 'priority' => 3],
        ]]);
    }

    private function rss(): string
    {
        return '<rss version="2.0"><channel><item><title>Anime &amp; Manga</title><link>https://www.animenewsnetwork.com/news/example</link><guid>ann-123</guid><description>Resumo</description><pubDate>Fri, 16 Oct 2026 11:00:00 -0400</pubDate></item></channel></rss>';
    }

    private function atom(): string
    {
        return '<feed xmlns="http://www.w3.org/2005/Atom"><entry><title>新作アニメ</title><link rel="self" href="https://example.net/api/123"/><link rel="alternate" href="https://example.net/comic/news/123"/><id>123</id><summary type="html">Resumo &amp; anime</summary><updated>2026-10-17T00:00:00+09:00</updated></entry></feed>';
    }

    public function test_ann_rss_and_atom_are_normalized_without_persisting_posts(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*animenewsnetwork.com/news/rss.xml' => Http::response($this->rss(), 200, ['Content-Type' => 'application/rss+xml']),
            'example.net/comic/feed/news' => Http::response($this->atom(), 200, ['Content-Type' => 'application/xml']),
        ]);
        $service = app(AnimeNewsFeedService::class);
        $ann = $service->latest('anime-news-network')->sole();
        $this->assertSame('anime-news-network-ann-123', $ann['id']);
        $this->assertSame('Anime & Manga', $ann['title']);
        $this->assertSame('2026-10-16T11:00:00-04:00', $ann['published_at']);
        $this->assertNull($ann['image']);
        $atom = $service->latest('sample-atom')->sole();
        $this->assertSame('sample-atom-123', $atom['id']);
        $this->assertSame('https://example.net/comic/news/123', $atom['url']);
        $this->assertSame('Resumo & anime', $atom['excerpt']);
        $this->assertSame('ja', $atom['source']['language']);
        $this->assertSame('2026-10-17T00:00:00+09:00', $atom['published_at']);
    }

    public function test_repeated_refreshes_and_duplicate_ids_or_urls_do_not_duplicate_items(): void
    {
        $item = '<item><title>Example</title><link>https://example.com/news/1</link><guid>1</guid></item>';
        Http::fake(['*' => Http::response('<rss><channel>'.$item.$item.str_replace('<guid>1</guid>', '<guid>2</guid>', $item).'</channel></rss>')]);
        $service = app(AnimeNewsFeedService::class);
        $first = $service->latest('anime-news-network');
        $this->assertCount(1, $first);
        $this->assertSame($first->all(), $service->latest('anime-news-network')->all());
    }

    public function test_source_failure_does_not_interrupt_other_sources(): void
    {
        Log::spy();
        Http::fake([
            '*animenewsnetwork.com/*' => Http::response('Unavailable', 503),
            'example.net/*' => Http::response($this->atom()),
            '*crunchyrollsvc.com/*' => Http::response('<broken'),
            '*' => Http::response([]),
        ]);
        $this->assertSame('sample-atom', app(AnimeNewsFeedService::class)->latest()->sole()['source']['slug']);
        Log::shouldHaveReceived('warning')->withArgs(fn ($message, $context) => $context['source'] === 'anime-news-network')->once();
    }

    public function test_existing_wordpress_and_rss_images_still_work(): void
    {
        Http::fake([
            '*animeunited.com.br/*' => Http::response([['id' => 10, 'title' => ['rendered' => 'Título'], 'link' => 'https://example.com/wp', 'date_gmt' => '2026-10-16T10:00:00', '_embedded' => ['wp:featuredmedia' => [['source_url' => 'https://example.com/image.jpg']]]]]),
            '*crunchyrollsvc.com/*' => Http::response(str_replace('</item>', '<enclosure url="https://example.com/rss.jpg" type="image/jpeg"/></item>', $this->rss())),
        ]);
        $service = app(AnimeNewsFeedService::class);
        $this->assertSame('https://example.com/image.jpg', $service->latest('anime-united')->sole()['image']);
        $this->assertSame('https://example.com/rss.jpg', $service->latest('crunchyroll')->sole()['image']);
    }
}
