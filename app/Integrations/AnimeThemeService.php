<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnimeThemeService
{
    private $baseUrl = 'https://api.animethemes.moe';

    public function search(string $query)
    {
        $query = mb_strtolower(trim($query));

        if ($query === '') return collect();

        $cacheKey = 'animethemes.musics.v2.' . md5($query);

        if (Cache::has($cacheKey)) return Cache::get($cacheKey);

        $response = Http::timeout(5)->withOptions([
            'verify' => false,
        ])->get("{$this->baseUrl}/search", [
            'q' => $query,
            'include' => [
                'animetheme' => 'anime.images,song.artists',
            ],
        ]);

        if ($response->failed()) {
            Log::warning('AnimeTheme API is not service' . $response->status());
            return null;
        }

        $data = $response->json();

        $formatted = collect($data['search']['animethemes'] ?? [])
            ->filter(fn ($item) => isset($item['anime']))
            ->groupBy('anime.id')
            ->map(function ($themes) {
                $anime = $themes->first()['anime'];

                return [
                    'anime' => $anime['name'],
                    'banner' => $anime['images'][0]['link'] ?? null,
                    'musics' => $themes->map(function ($music) {
                        return [
                            'type' => $music['type'],
                            'title' => $music['song']['title'] ?? null,
                            'artists' => collect($music['song']['artists'] ?? [])->pluck('name')->join(', ') ?: 'Não informado'
                        ];
                    })->values(),
                ];
            })
            ->values();

        Cache::put($cacheKey, $formatted, now()->addHours(12));

        return $formatted;
    }

    public function searchAnime(string $query)
    {
        $query = mb_strtolower(trim($query));

        if ($query === '') return collect();

        $cacheKey = 'animethemes.animes.v2.' . md5($query);

        if (Cache::has($cacheKey)) return Cache::get($cacheKey);

        $response = Http::timeout(5)->withOptions([
            'verify' => false,
        ])->get("{$this->baseUrl}/search", [
            'q' => $query,
            'include' => [
                'animetheme' => 'anime.images',
            ],
        ]);

        if ($response->failed()) {
            Log::warning('AnimeTheme API is not service' . $response->status());
            return null;
        }

        $data = $response->json();

        $formatted = collect($data['search']['animethemes'] ?? [])
            ->filter(fn ($item) => isset($item['anime']))
            ->map(fn ($item) => $item['anime'])
            ->unique(fn ($anime) => $anime['id'] ?? $anime['anime_id'] ?? $anime['slug'] ?? $anime['name'])
            ->map(function ($anime) {
                $image = collect($anime['images'] ?? [])
                    ->filter(fn ($image) => filled($image['link'] ?? null))
                    ->sortByDesc(fn ($image) => $image['size'] ?? 0)
                    ->first()
                    ?? ($anime['images'][0] ?? null);

                return [
                    'anime_theme_list_id' => (string) ($anime['id'] ?? $anime['anime_id'] ?? ''),
                    'slug' => $anime['slug'] ?? null,
                    'name' => $anime['name'] ?? null,
                    'image' => $image['link'] ?? null,
                    'metadata' => [
                        'year' => $anime['year'] ?? null,
                        'season' => $anime['season'] ?? null,
                        'media_format' => $anime['media_format'] ?? null,
                    ],
                ];
            })
            ->filter(fn ($anime) => filled($anime['anime_theme_list_id']) && filled($anime['name']))
            ->values();

        Cache::put($cacheKey, $formatted, now()->addHours(12));

        return $formatted;
    }
}
