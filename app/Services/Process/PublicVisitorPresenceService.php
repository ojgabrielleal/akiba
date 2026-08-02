<?php

namespace App\Services\Process;

use App\Models\OAuthAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicVisitorPresenceService
{
    private const ONLINE_WINDOW_SECONDS = 90;
    private const VISITOR_INDEX_KEY = 'public_presence:visitors';
    private const VISITOR_KEY_PREFIX = 'public_presence:visitor:';

    public function heartbeat(Request $request, array $data): array
    {
        $url = $this->limit($data['url'] ?? null, 500);
        $path = $this->limit($data['path'] ?? parse_url($url ?? '', PHP_URL_PATH), 255);
        $visitorToken = $data['visitor_token'];
        $visitor = [
            'ip_address' => $request->ip(),
            'user_agent' => $this->limit($request->userAgent(), 500),
            'page_title' => $this->limit($data['title'] ?? null, 255),
            'page_path' => $path ?: '/',
            'page_url' => $url,
            'referrer' => $this->limit($data['referrer'] ?? null, 500),
            'listening' => (bool) ($data['listening'] ?? false),
            'player_loading' => (bool) ($data['player_loading'] ?? false),
            'identity' => $this->identity($request),
            'last_seen_at' => now()->toISOString(),
        ];

        Cache::put($this->visitorKey($visitorToken), $visitor, now()->addSeconds(self::ONLINE_WINDOW_SECONDS));
        $this->rememberVisitor($visitorToken);

        return $visitor;
    }

    public function summary(): array
    {
        $visitors = $this->online();
        $pages = $visitors
            ->groupBy(fn (array $visitor) => $visitor['page_path'] ?: '/')
            ->map(function ($items, string $path) {
                $latest = $items->sortByDesc('last_seen_at')->first();

                return [
                    'path' => $path,
                    'title' => $latest['page_title'] ?? null,
                    'url' => $latest['page_url'] ?? null,
                    'visitors' => $items->count(),
                    'last_seen_at' => isset($latest['last_seen_at'])
                        ? Carbon::parse($latest['last_seen_at'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
                        : null,
                ];
            })
            ->sortByDesc('visitors')
            ->values()
            ->all();

        return [
            'total_conected' => $visitors->count(),
            'listeners' => $visitors->where('listening', true)->count(),
            'recognized_users' => $visitors->reject(fn (array $visitor) => data_get($visitor, 'identity.type') === 'anonymous')->count(),
            'anonimus_user' => $visitors->where('identity.type', 'anonymous')->count(),
            'window_seconds' => self::ONLINE_WINDOW_SECONDS,
            'pages' => $pages,
            'visitors' => $visitors->map(fn (array $visitor) => [
                'identity' => $visitor['identity'],
                'page_path' => $visitor['page_path'],
                'page_title' => $visitor['page_title'],
                'listening' => $visitor['listening'],
                'player_loading' => $visitor['player_loading'],
                'last_seen_at' => Carbon::parse($visitor['last_seen_at'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s'),
            ])->all(),
            'updated_at' => now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s'),
        ];
    }

    private function online()
    {
        $tokens = Cache::get(self::VISITOR_INDEX_KEY, []);

        return collect($tokens)
            ->unique()
            ->mapWithKeys(fn (string $token) => [$token => Cache::get($this->visitorKey($token))])
            ->filter()
            ->tap(fn ($visitors) => $this->syncVisitorIndex($visitors->keys()->all()))
            ->values();
    }

    private function rememberVisitor(string $visitorToken): void
    {
        $tokens = Cache::get(self::VISITOR_INDEX_KEY, []);
        $tokens[] = $visitorToken;

        $this->syncVisitorIndex($tokens);
    }

    private function syncVisitorIndex(array $tokens): void
    {
        Cache::put(
            self::VISITOR_INDEX_KEY,
            collect($tokens)->unique()->values()->all(),
            now()->addDay()
        );
    }

    private function visitorKey(string $visitorToken): string
    {
        return self::VISITOR_KEY_PREFIX.$visitorToken;
    }

    private function identity(Request $request): array
    {
        $user = $request->user() ?: $request->attributes->get('member_user');
        $oauthAccount = $request->attributes->get('oauth_account');

        if ($user instanceof User) {
            return [
                'type' => 'member',
                'uuid' => $user->uuid,
                'provider' => 'internal',
                'name' => $user->nickname ?: $user->name,
                'avatar' => $user->avatar,
                'gender' => $user->gender,
            ];
        }

        if ($oauthAccount instanceof OAuthAccount) {
            return [
                'type' => 'oauth',
                'uuid' => $oauthAccount->uuid,
                'provider' => $oauthAccount->provider,
                'name' => $oauthAccount->nickname ?: $oauthAccount->username,
                'avatar' => $oauthAccount->avatar,
                'gender' => null,
            ];
        }

        return [
            'type' => 'anonymous',
            'uuid' => null,
            'provider' => null,
            'name' => 'Anônimo',
            'avatar' => null,
            'gender' => null,
        ];
    }

    private function limit(?string $value, int $limit): ?string
    {
        return $value === null ? null : Str::limit($value, $limit, '');
    }
}
