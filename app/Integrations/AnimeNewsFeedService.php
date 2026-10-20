<?php

namespace App\Integrations;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnimeNewsFeedService
{

    public function sources(): Collection
    {
        return collect(config('services.anime_news_sources', []))
            ->sortBy([
                ['priority', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    public function latest(?string $sourceSlug = null, int $limit = 10): Collection
    {
        $sources = $this->sources()
            ->when($sourceSlug, fn ($sources) => $sources->where('slug', $sourceSlug))
            ->values();

        return $sources
            ->flatMap(fn (array $source) => $this->latestFromSource($source, $this->sourceLimit($source, $limit)))
            ->unique('id')
            ->unique('url')
            ->sortByDesc(fn (array $post) => $this->publishedTimestamp($post['published_at'] ?? null))
            ->take($limit)
            ->values();
    }

    public function paginate(?string $sourceSlug = null, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $items = $this->latest($sourceSlug, 60);
        $page = max(1, $page);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->map(function (array $post) {
                if (blank($post['image'])) {
                    $images = app(NewsArticleImageService::class);
                    $remoteImage = $images->resolve($post['source']['slug'], $post['url']);
                    $post['image'] = $remoteImage ? $images->preview($post['source']['slug'], $remoteImage) : null;
                }
                return $post;
            })->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'feed_page',
                'query' => request()->except('feed_page'),
            ]
        );
    }

    private function latestFromSource(array $source, int $limit): Collection
    {
        try {
            if (($source['type'] ?? 'wordpress') === 'rss') {
                return $this->latestFromRssSource($source, $limit);
            }

            return $this->latestFromWordpressSource($source, $limit);
        } catch (\Throwable $exception) {
            Log::warning('Anime news feed request failed', [
                'source' => $source['slug'],
                'message' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    private function sourceLimit(array $source, int $limit): int
    {
        if (($source['type'] ?? 'wordpress') === 'rss') {
            return min($limit * 3, 60);
        }

        return min($limit * 2, 20);
    }

    private function latestFromWordpressSource(array $source, int $limit): Collection
    {
        $response = Http::timeout(8)->withOptions([
            'verify' => false,
        ])->get($source['posts_url'], [
            'per_page' => $limit,
            '_embed' => 1,
        ]);

        if ($response->failed()) {
            Log::warning('Anime news feed unavailable', [
                'source' => $source['slug'],
                'status' => $response->status(),
            ]);

            return collect();
        }

        return collect($response->json())
            ->map(fn (array $post) => $this->formatWordpressPost($source, $post))
            ->filter()
            ->values();
    }

    private function latestFromRssSource(array $source, int $limit): Collection
    {
        $response = Http::timeout(8)->get($source['feed_url']);

        if ($response->failed()) {
            Log::warning('Anime RSS feed unavailable', [
                'source' => $source['slug'],
                'status' => $response->status(),
            ]);

            return collect();
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $rss = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($rss === false) {
            Log::warning('Anime RSS feed invalid', [
                'source' => $source['slug'],
            ]);

            return collect();
        }

        if ($rss->getName() === 'feed') {
            return collect(iterator_to_array($rss->children('http://www.w3.org/2005/Atom')->entry, false))
                ->take($limit)
                ->map(fn ($item) => $this->formatAtomPost($source, $item))
                ->filter()
                ->values();
        }

        $items = isset($rss->channel->item)
            ? iterator_to_array($rss->channel->item, false)
            : [];

        return collect($items)
            ->take($limit)
            ->map(fn ($item) => $this->formatRssPost($source, $item))
            ->filter()
            ->values();
    }

    private function formatWordpressPost(array $source, array $post): ?array
    {
        $title = $this->clean($post['title']['rendered'] ?? null);
        $url = $post['link'] ?? null;

        if (blank($title) || blank($url)) {
            return null;
        }

        $content = $post['content']['rendered'] ?? null;
        $excerpt = $this->clean($post['excerpt']['rendered'] ?? null);

        return $this->formatPost($source, [
            'id' => $post['id'] ?? md5($url),
            'title' => $title,
            'url' => $url,
            'excerpt' => Str::limit($excerpt, 240),
            'content' => $content,
            'published_at' => $post['date_gmt'] ?? $post['date'] ?? null,
            'image' => $this->featuredImage($post),
        ]);
    }

    private function formatRssPost(array $source, \SimpleXMLElement $item): ?array
    {
        $contentNamespace = $item->children('http://purl.org/rss/1.0/modules/content/');
        $mediaNamespace = $item->children('http://search.yahoo.com/mrss/');

        $title = $this->clean((string) $item->title);
        $url = trim((string) $item->link);

        if (blank($title) || blank($url)) {
            return null;
        }

        $content = (string) ($contentNamespace->encoded ?? $item->description ?? '');
        $excerpt = $this->clean((string) ($item->description ?? $content));
        $guid = trim((string) $item->guid) ?: md5($url);
        $image = $this->rssImage($item, $mediaNamespace);

        return $this->formatPost($source, [
            'id' => $guid,
            'title' => $title,
            'url' => $url,
            'excerpt' => Str::limit($excerpt, 240),
            'content' => $content,
            'published_at' => trim((string) ($item->pubDate ?? null)) ?: null,
            'image' => $image,
        ]);
    }

    private function formatAtomPost(array $source, \SimpleXMLElement $item): ?array
    {
        $title = $this->clean((string) $item->title);
        $url = null;
        $image = null;
        foreach ($item->link as $link) {
            $attributes = $link->attributes();
            $rel = (string) $attributes->rel;
            if ($rel === '' || $rel === 'alternate') {
                $url ??= trim((string) $attributes->href);
            }
            if ($rel === 'enclosure' && str_starts_with((string) $attributes->type, 'image/')) {
                $image = (string) $attributes->href;
            }
        }

        if (blank($title) || blank($url)) {
            return null;
        }

        return $this->formatPost($source, [
            'id' => trim((string) $item->id) ?: md5($url),
            'title' => $title,
            'url' => $url,
            'excerpt' => Str::limit($this->clean((string) ($item->summary ?? $item->content)), 240),
            'content' => (string) ($item->content ?? $item->summary),
            // Comic Natalie supplies updated, but no published timestamp.
            'published_at' => trim((string) ($item->published ?? $item->updated)) ?: null,
            'image' => $image ?? $this->rssImage($item, $item->children('http://search.yahoo.com/mrss/')),
        ]);
    }

    private function formatPost(array $source, array $post): array
    {
        return [
            'id' => $source['slug'] . '-' . ($post['id'] ?? md5($post['url'])),
            'source' => [
                'name' => $source['name'],
                'slug' => $source['slug'],
                'language' => $source['language'],
                'base_url' => $source['base_url'],
            ],
            'title' => $post['title'],
            'url' => $post['url'],
            'excerpt' => $post['excerpt'],
            'content' => $post['content'],
            'published_at' => $this->formatPublishedAt($post['published_at'] ?? null),
            'image' => $post['image'],
        ];
    }

    private function rssImage(\SimpleXMLElement $item, \SimpleXMLElement $mediaNamespace): ?string
    {
        $enclosureAttributes = $item->enclosure?->attributes();

        if (filled((string) ($enclosureAttributes?->url ?? null))) {
            return (string) $enclosureAttributes->url;
        }

        foreach (['content', 'thumbnail'] as $node) {
            $attributes = $mediaNamespace->{$node}?->attributes();

            if (filled((string) ($attributes?->url ?? null))) {
                return (string) $attributes->url;
            }
        }

        return null;
    }

    private function featuredImage(array $post): ?string
    {
        return data_get($post, '_embedded.wp:featuredmedia.0.source_url')
            ?? data_get($post, 'yoast_head_json.og_image.0.url');
    }

    private function publishedTimestamp(?string $publishedAt): int
    {
        if (blank($publishedAt)) {
            return 0;
        }

        try {
            return Carbon::parse($publishedAt)->timestamp;
        } catch (\Throwable) {
            return 0;
        }
    }

    private function formatPublishedAt(?string $publishedAt): ?string
    {
        if (blank($publishedAt)) {
            return null;
        }

        try {
            return Carbon::parse($publishedAt)->toIso8601String();
        } catch (\Throwable) {
            return $publishedAt;
        }
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim(html_entity_decode(strip_tags($value)));
    }
}
