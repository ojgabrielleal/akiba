<?php

namespace App\Services;

use App\Integrations\IpLocation;
use App\Models\PageView;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use App\Support\AuthenticatedMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PageViewService
{
    public function __construct(private IpLocation $ipLocation) {}

    public function storePage(string $pageName, Request $request): PageView
    {
        $actor = AuthenticatedMember::fromRequest($request);

        return PageView::query()->create([
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'ip_address' => $request->ip(),
            ...$this->ipLocation->find($request->ip()),
            ...$this->trafficAttribution($request),
            'page_name' => $pageName,
            'page_url' => $request->url(),
        ]);
    }

    public function store(Model $viewable, Request $request): ?PageView
    {
        $actor = AuthenticatedMember::fromRequest($request);

        if ($viewable instanceof Post
            && $actor instanceof User
            && (int) $viewable->user_id === (int) $actor->getKey()) {
            return null;
        }

        $location = $this->ipLocation->find($request->ip());

        return DB::transaction(fn () => $viewable->views()->create([
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'ip_address' => $request->ip(),
            ...$location,
            ...$this->trafficAttribution($request),
            'page_name' => $viewable->title ?? null,
            'page_url' => $request->fullUrl(),
        ]));
    }

    public function geographyReport(array $filters = []): array
    {
        $period = $filters['period'] ?? 'today';
        $visitorType = $filters['visitor_type'] ?? 'all';
        $pageType = $filters['page_type'] ?? 'all';

        $baseQuery = PageView::query()
            ->when($period === 'today', fn ($query) => $query
                ->where('created_at', '>=', now()->startOfDay()))
            ->when(is_int($period), fn ($query) => $query
                ->where('created_at', '>=', now()->subDays($period)))
            ->when($visitorType === 'recognized', fn ($query) => $query->whereNotNull('actor_id'))
            ->when($visitorType === 'anonymous', fn ($query) => $query->whereNull('actor_id'))
            ->when($pageType === 'pages', fn ($query) => $query->whereNull('viewable_type'))
            ->when($pageType === 'podcasts', fn ($query) => $query
                ->where('viewable_type', (new Podcast)->getMorphClass()))
            ->when(in_array($pageType, ['post', 'review', 'event'], true), fn ($query) => $query
                ->where('viewable_type', (new Post)->getMorphClass())
                ->whereIn('viewable_id', Post::query()
                    ->select('id')
                    ->where('module', $pageType)));

        $totalViews = (clone $baseQuery)->count();
        $locatedViews = (clone $baseQuery)->whereNotNull('country_code')->count();

        $countries = (clone $baseQuery)
            ->whereNotNull('country_code')
            ->select('country_code', DB::raw('COUNT(*) as views'))
            ->groupBy('country_code')
            ->orderByDesc('views')
            ->get()
            ->map(fn (PageView $view): array => [
                'code' => $view->country_code,
                'views' => (int) $view->views,
            ])
            ->all();

        $cities = (clone $baseQuery)
            ->whereNotNull('country_code')
            ->whereNotNull('city')
            ->select([
                'country_code',
                'region_code',
                'city',
                DB::raw('COUNT(*) as views'),
            ])
            ->groupBy('country_code', 'region_code', 'city')
            ->orderByDesc('views')
            ->limit(20)
            ->get()
            ->map(fn (PageView $view): array => [
                'country_code' => $view->country_code,
                'region_code' => $view->region_code,
                'city' => $view->city,
                'views' => (int) $view->views,
            ])
            ->all();

        $sources = (clone $baseQuery)
            ->select('traffic_source', DB::raw('COUNT(*) as views'))
            ->groupBy('traffic_source')
            ->orderByDesc('views')
            ->limit(20)
            ->get()
            ->map(fn (PageView $view): array => [
                'source' => $view->traffic_source ?: 'direct',
                'label' => $this->trafficSourceLabel($view->traffic_source ?: 'direct'),
                'views' => (int) $view->views,
            ])
            ->all();

        $viewers = $visitorType !== 'anonymous'
            ? (clone $baseQuery)
                ->whereNotNull('actor_id')
                ->select([
                    'actor_type',
                    'actor_id',
                    DB::raw('COUNT(*) as views'),
                    DB::raw('MAX(created_at) as last_view'),
                ])
                ->groupBy('actor_type', 'actor_id')
                ->orderByDesc('views')
                ->limit(20)
                ->with('actor')
                ->get()
                ->map(function (PageView $view): array {
                    $actor = $view->actor;

                    return [
                        'key' => hash('sha256', $view->actor_type.':'.$view->actor_id),
                        'name' => $actor?->nickname ?? $actor?->name ?? $actor?->username ?? 'Sem nome',
                        'avatar' => $actor?->avatar,
                        'views' => (int) $view->views,
                        'last_view' => $view->last_view
                            ? Carbon::parse($view->last_view)
                                ->setTimezone('America/Sao_Paulo')
                                ->format('d/m/Y H:i')
                            : null,
                    ];
                })
                ->all()
            : [];

        return [
            'period' => $period,
            'visitor_type' => $visitorType,
            'page_type' => $pageType,
            'total_views' => $totalViews,
            'located_views' => $locatedViews,
            'unknown_views' => $totalViews - $locatedViews,
            'countries' => $countries,
            'cities' => $cities,
            'sources' => $sources,
            'viewers' => $viewers,
        ];
    }

    private function trafficAttribution(Request $request): array
    {
        $utmSource = $this->limitedString($request->query('utm_source'));
        $utmMedium = $this->limitedString($request->query('utm_medium'));
        $utmCampaign = $this->limitedString($request->query('utm_campaign'));
        $referrerUrl = $this->limitedString($request->headers->get('referer'), 2048);
        $referrerHost = $referrerUrl
            ? strtolower((string) parse_url($referrerUrl, PHP_URL_HOST))
            : null;

        if ($referrerHost) {
            $referrerHost = preg_replace('/^(www\.|m\.)/i', '', $referrerHost);
        }

        $source = $utmSource
            ? $this->normalizeTrafficSource($utmSource)
            : $this->sourceFromReferrer($referrerHost, strtolower($request->getHost()));

        return [
            'traffic_source' => $source,
            'referrer_host' => $referrerHost ?: null,
            'referrer_url' => $referrerUrl,
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
        ];
    }

    private function sourceFromReferrer(?string $referrerHost, string $siteHost): string
    {
        if (! $referrerHost) {
            return 'direct';
        }

        $siteHost = preg_replace('/^(www\.|m\.)/i', '', $siteHost);

        if ($referrerHost === $siteHost) {
            return 'internal';
        }

        foreach ([
            'google' => ['google.'],
            'bing' => ['bing.com'],
            'duckduckgo' => ['duckduckgo.com'],
            'yahoo' => ['search.yahoo.', 'yahoo.com'],
            'instagram' => ['instagram.com'],
            'facebook' => ['facebook.com', 'fb.com'],
            'x' => ['t.co', 'twitter.com', 'x.com'],
            'youtube' => ['youtube.com', 'youtu.be'],
        ] as $source => $domains) {
            if (collect($domains)->contains(fn (string $domain) => str_contains($referrerHost, $domain))) {
                return $source;
            }
        }

        return mb_substr($referrerHost, 0, 120);
    }

    private function normalizeTrafficSource(string $source): string
    {
        $source = strtolower(trim($source));

        return match ($source) {
            'ig' => 'instagram',
            'fb', 'meta' => 'facebook',
            'twitter' => 'x',
            default => mb_substr($source, 0, 120),
        };
    }

    private function trafficSourceLabel(string $source): string
    {
        return match ($source) {
            'direct' => 'Acesso direto',
            'internal' => 'Navegação interna',
            'google' => 'Google',
            'bing' => 'Bing',
            'duckduckgo' => 'DuckDuckGo',
            'yahoo' => 'Yahoo',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'x' => 'X / Twitter',
            'youtube' => 'YouTube',
            default => $source,
        };
    }

    private function limitedString(mixed $value, int $limit = 255): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return mb_substr(trim($value), 0, $limit);
    }

    public function pageAccessReport(array $filters = []): LengthAwarePaginator
    {
        $period = $filters['period'] ?? null;
        $visitorType = $filters['visitor_type'] ?? 'all';
        $search = $filters['search'] ?? '';
        $kind = $filters['kind'] ?? 'pages';
        $podcastType = (new Podcast)->getMorphClass();

        return PageView::query()
            ->when(
                $kind === 'podcasts',
                fn ($query) => $query->where('viewable_type', $podcastType),
                fn ($query) => $query->whereNull('viewable_type'),
            )
            ->when($period === 'today', fn ($query) => $query
                ->where('created_at', '>=', now()->startOfDay()))
            ->when(is_int($period), fn ($query) => $query
                ->where('created_at', '>=', now()->subDays($period)))
            ->when($visitorType === 'recognized', fn ($query) => $query->whereNotNull('actor_id'))
            ->when($visitorType === 'anonymous', fn ($query) => $query->whereNull('actor_id'))
            ->when($search, fn ($query, string $value) => $query
                ->where('page_name', 'like', '%'.addcslashes($value, '\\%_').'%'))
            ->selectRaw("COALESCE(viewable_type, 'page') as item_type")
            ->selectRaw("COALESCE(CAST(viewable_id AS CHAR), page_name) as item_key")
            ->selectRaw('MAX(page_name) as title')
            ->selectRaw('MAX(page_url) as url')
            ->selectRaw('COUNT(*) as views')
            ->selectRaw('SUM(actor_id IS NOT NULL) as recognized_views')
            ->selectRaw('SUM(actor_id IS NULL) as anonymous_views')
            ->selectRaw('MAX(created_at) as last_view_at')
            ->groupByRaw("COALESCE(viewable_type, 'page'), COALESCE(CAST(viewable_id AS CHAR), page_name)")
            ->when(
                ($filters['sort'] ?? '') === 'recent',
                fn ($query) => $query->orderByDesc('last_view_at'),
                fn ($query) => $query->orderByDesc('views'),
            )
            ->paginate(5, ['*'], $kind === 'podcasts' ? 'podcasts_page' : 'pages_page')
            ->withQueryString()
            ->through(fn (PageView $view): array => [
                'key' => hash('sha256', $view->item_type.':'.$view->item_key),
                'title' => $view->title,
                'url' => $view->url,
                'kind' => $view->item_type === 'page' ? 'Página' : 'Podcast',
                'views' => (int) $view->views,
                'recognized_views' => (int) $view->recognized_views,
                'anonymous_views' => (int) $view->anonymous_views,
                'last_view' => $view->last_view_at
                    ? Carbon::parse($view->last_view_at)
                        ->setTimezone('America/Sao_Paulo')
                        ->format('d/m/Y H:i')
                    : null,
            ]);
    }
}
