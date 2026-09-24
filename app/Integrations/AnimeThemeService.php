<?php

namespace App\Integrations;

use App\Models\Anime;
use App\Models\Music;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnimeThemeService
{
    private $baseUrl = 'https://api.animethemes.moe';

    public function search(string $query): array
    {
        $query = mb_strtolower(trim($query));

        if ($query === '') {
            return [
                'results' => [],
                'api_available' => $this->isAvailable(),
            ];
        }

        $localResults = $this->searchLocalMusics($query);
        $shouldSearchApi = $localResults->isEmpty()
            || $localResults->contains(fn ($item) => ($item['discography_status'] ?? Anime::DISCOGRAPHY_UNKNOWN) !== Anime::DISCOGRAPHY_COMPLETE);
        $apiAvailable = $shouldSearchApi ? $this->isAvailable() : true;

        if (! $shouldSearchApi || ! $apiAvailable) {
            return [
                'results' => $localResults->values(),
                'api_available' => $apiAvailable,
            ];
        }

        $cacheKey = 'animethemes.musics.v2.' . md5($query);

        $externalResults = Cache::get($cacheKey);

        if (! $externalResults) {
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

                return [
                    'results' => $localResults->values(),
                    'api_available' => false,
                ];
            }

            $data = $response->json();

            $externalResults = collect($data['search']['animethemes'] ?? [])
                ->filter(fn ($item) => isset($item['anime']))
                ->groupBy('anime.id')
                ->map(function ($themes) {
                    $anime = $themes->first()['anime'];

                    return [
                        'anime' => $anime['name'],
                        'banner' => $this->animeImage($anime),
                        'discography_status' => Anime::DISCOGRAPHY_UNKNOWN,
                        'musics' => $themes->map(function ($music) {
                            $type = $music['type'] ?? null;

                            if (! in_array($type, ['OP', 'ED', 'OVA'], true)) {
                                $type = 'OVA';
                            }

                            return [
                                'type' => $type,
                                'title' => $music['song']['title'] ?? null,
                                'artists' => collect($music['song']['artists'] ?? [])->pluck('name')->join(', ') ?: 'Não informado',
                                'is_manual' => false,
                            ];
                        })->filter(fn ($music) => filled($music['title']))->values(),
                    ];
                })
                ->values();

            Cache::put($cacheKey, $externalResults, now()->addHours(12));
        }

        $this->syncDiscographyStatus($externalResults);

        return [
            'results' => $this->mergeResults($localResults, collect($externalResults))->values(),
            'api_available' => true,
        ];
    }

    private function isAvailable(): bool
    {
        return Cache::remember('animethemes.available', now()->addMinutes(2), function () {
            try {
                return Http::timeout(2)->withOptions([
                    'verify' => false,
                ])->get("{$this->baseUrl}/search", [
                    'q' => 'naruto',
                ])->successful();
            } catch (\Throwable $exception) {
                Log::warning('AnimeTheme API availability check failed', [
                    'message' => $exception->getMessage(),
                ]);

                return false;
            }
        });
    }

    private function searchLocalMusics(string $query)
    {
        return Music::query()
            ->with('anime')
            ->where('is_manual', false)
            ->where(function ($builder) use ($query) {
                $like = "%{$query}%";

                $builder->where('name', 'like', $like)
                    ->orWhere('artist', 'like', $like)
                    ->orWhereHas('anime', fn ($animeQuery) => $animeQuery->where('name', 'like', $like));
            })
            ->get()
            ->filter(fn (Music $music) => $music->anime)
            ->groupBy('anime_id')
            ->map(function ($musics) {
                $anime = $musics->first()->anime;

                return [
                    'anime' => $anime->name,
                    'banner' => $anime->image,
                    'discography_status' => $anime->discography_status,
                    'musics' => $musics
                        ->map(fn (Music $music) => [
                            'type' => $music->type,
                            'title' => $music->name,
                            'artists' => $music->artist,
                            'is_manual' => false,
                        ])
                        ->values(),
                ];
            })
            ->values();
    }

    private function mergeResults($localResults, $externalResults)
    {
        return $localResults
            ->concat($externalResults)
            ->groupBy(fn ($item) => Str::slug($item['anime'] ?? ''))
            ->map(function ($items) {
                $first = $items->first();
                $musics = $items
                    ->flatMap(fn ($item) => $item['musics'] ?? [])
                    ->filter(fn ($music) => filled($music['title'] ?? null))
                    ->unique(fn ($music) => implode('|', [
                        $music['type'] ?? '',
                        Str::lower($music['title'] ?? ''),
                    ]))
                    ->values();

                return [
                    'anime' => $first['anime'],
                    'banner' => $first['banner'] ?? null,
                    'discography_status' => $first['discography_status'] ?? Anime::DISCOGRAPHY_UNKNOWN,
                    'musics' => $musics,
                ];
            });
    }

    private function syncDiscographyStatus($externalResults): void
    {
        collect($externalResults)->each(function ($item) {
            $animeName = $item['anime'] ?? null;

            if (! $animeName) {
                return;
            }

            $anime = Anime::where('slug', Str::slug($animeName))->first();

            if (! $anime) {
                return;
            }

            $externalTotal = collect($item['musics'] ?? [])->count();
            $localTotal = $anime->musics()->where('is_manual', false)->count();

            $anime->update([
                'discography_status' => $externalTotal > 0 && $localTotal >= $externalTotal
                    ? Anime::DISCOGRAPHY_COMPLETE
                    : Anime::DISCOGRAPHY_PARTIAL,
                'discography_checked_at' => now(),
                'image' => $anime->image ?: ($item['banner'] ?? null),
            ]);
        });
    }

    private function animeImage(array $anime): ?string
    {
        $image = collect($anime['images'] ?? [])
            ->filter(fn ($image) => filled($image['link'] ?? null))
            ->sortByDesc(fn ($image) => $image['size'] ?? 0)
            ->first()
            ?? ($anime['images'][0] ?? null);

        return $image['link'] ?? null;
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
