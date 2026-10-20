<?php

namespace App\Integrations;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NewsArticleImageService
{
    public function resolve(string $source, string $url): ?string
    {
        $settings = config("services.news_article_images.{$source}");
        if (! $settings || ! $this->allowedArticle($url, $settings)) {
            return null;
        }

        $key = 'news-article-image:v1:'.hash('sha256', $url);
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached['image'];
        }

        try {
            // Serialize each source, including requests from concurrent panel visits.
            return Cache::lock("news-article-image:{$source}:lock", 30)->block(15, function () use ($source, $url, $settings, $key) {
                $cached = Cache::get($key);
                if (is_array($cached)) {
                    return $cached['image'];
                }

                $image = $this->fetch($source, $url, $settings);
                // Cache misses too, so unavailable articles are not fetched on every refresh.
                Cache::put($key, ['image' => $image], $image ? now()->addDay() : now()->addHour());

                return $image;
            });
        } catch (\Throwable $exception) {
            Log::warning('News article image unavailable', ['source' => $source, 'message' => $exception->getMessage()]);
            return null;
        }
    }

    public function preview(string $source, string $url): ?string
    {
        $hosts = config("services.news_article_images.{$source}.image_hosts", []);
        $parts = parse_url($url);
        if (! $parts || ($parts['scheme'] ?? '') !== 'https'
            || ! in_array($parts['host'] ?? '', $hosts, true)
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }

        $key = 'news-image-preview:v1:'.hash('sha256', $url);
        $cached = Cache::get($key);
        if (is_array($cached) && ($cached['path'] === null || Storage::disk('public')->exists($cached['path']))) {
            return $cached['path'] ? '/storage/'.$cached['path'] : null;
        }

        try {
            $response = Http::connectTimeout(2)->timeout(5)->withoutRedirecting()
                ->withUserAgent('AkibaNewsPreview/1.0')->get($url);
            $body = $response->body();
            $info = $response->successful() && strlen($body) <= 5000000
                ? @getimagesizefromstring($body) : false;
            $extension = $info ? match ($info['mime']) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => null,
            } : null;

            if ($extension) {
                $path = 'news-feed/'.hash('sha256', $url).'.'.$extension;
                if (Storage::disk('public')->put($path, $body)) {
                    Cache::put($key, ['path' => $path], now()->addDay());
                    return '/storage/'.$path;
                }
            }
            Log::warning('News image preview unavailable', ['source' => $source, 'status' => $response->status()]);
        } catch (\Throwable $exception) {
            Log::warning('News image preview failed', ['source' => $source, 'message' => $exception->getMessage()]);
        }

        Cache::put($key, ['path' => null], now()->addHour());
        return null;
    }

    private function fetch(string $source, string $url, array $settings): ?string
    {
        for ($redirect = 0; $redirect <= 2; $redirect++) {
            if (! $this->allowedArticle($url, $settings)) {
                return null;
            }

            $lastKey = "news-article-image:{$source}:last-request";
            $wait = (float) Cache::get($lastKey, 0) + $settings['interval'] - microtime(true);
            if ($wait > 0) {
                usleep((int) ($wait * 1000000));
            }
            Cache::put($lastKey, microtime(true), 60);

            try {
                $response = Http::connectTimeout(2)->timeout(4)->withoutRedirecting()
                    ->withUserAgent('AkibaNewsPreview/1.0')
                    ->accept('text/html')->get($url);
            } catch (\Throwable $exception) {
                Log::warning('News article image request failed', ['source' => $source, 'message' => $exception->getMessage()]);
                return null;
            }

            if ($response->redirect()) {
                $url = $this->absoluteUrl($url, $response->header('Location')) ?? '';
                continue;
            }
            if (! $response->successful()) {
                Log::warning('News article image HTTP failure', ['source' => $source, 'status' => $response->status()]);
                return null;
            }
            if (! str_contains(strtolower($response->header('Content-Type')), 'text/html') || strlen($response->body()) > 2000000) {
                return null;
            }

            return $this->extract($response->body(), $url);
        }

        return null;
    }

    private function allowedArticle(string $url, array $settings): bool
    {
        $parts = parse_url($url);
        return $parts !== false
            && ($parts['scheme'] ?? '') === 'https'
            && ($parts['host'] ?? '') === $settings['host']
            && ! isset($parts['user']) && ! isset($parts['pass'])
            && ! isset($parts['port'])
            && str_starts_with($parts['path'] ?? '', $settings['path'])
            && ! str_contains(rawurldecode($parts['path'] ?? ''), '..');
    }

    private function extract(string $html, string $url): ?string
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument;
            $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new DOMXPath($document);
            foreach ([
                '//meta[@property="og:image"]/@content',
                '//meta[@name="twitter:image" or @property="twitter:image"]/@content',
                '//article//img/@src',
            ] as $query) {
                foreach ($xpath->query($query) as $node) {
                    $image = $this->absoluteUrl($url, $node->nodeValue);
                    if ($image) {
                        return $image;
                    }
                }
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return null;
    }

    private function absoluteUrl(string $base, string $value): ?string
    {
        if (trim($value) === '') {
            return null;
        }
        try {
            $uri = UriResolver::resolve(new Uri($base), new Uri(trim($value)));
            if (! in_array($uri->getScheme(), ['https', 'http'], true) || ! $uri->getHost() || $uri->getUserInfo()) {
                return null;
            }
            return (string) $uri;
        } catch (\Throwable) {
            return null;
        }
    }
}
