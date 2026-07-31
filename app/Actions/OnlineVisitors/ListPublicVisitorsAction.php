<?php

namespace App\Actions\OnlineVisitors;

use Illuminate\Support\Facades\Cache;

class ListPublicVisitorsAction
{
    private const INDEX_KEY = 'online_visitors:index';

    private const VISITOR_KEY_PREFIX = 'online_visitors:';

    public function execute(): array
    {
        $visitorIds = Cache::get(self::INDEX_KEY, []);

        $visitors = collect($visitorIds)
            ->map(fn (string $visitorId) => Cache::get($this->visitorKey($visitorId)))
            ->filter()
            ->values();

        Cache::put(
            self::INDEX_KEY,
            $visitors->pluck('visitor_id')->values()->all(),
            now()->addDay(),
        );

        return [
            'conectados_agora' => $visitors->count(),
            'ouvindo_agora' => $visitors->where('is_listening', true)->count(),
            'contas_conectadas' => $this->connectedAccounts($visitors),
            'contas_ouvindo' => $this->connectedAccounts(
                $visitors->where('is_listening', true)->values(),
            ),
            'visitors' => $visitors->all(),
        ];
    }

    private function connectedAccounts($visitors): array
    {
        return $visitors
            ->pluck('oauth_account')
            ->filter()
            ->groupBy('id')
            ->map(function ($accounts) {
                $account = $accounts->first();

                return [
                    'id' => $account['id'],
                    'uuid' => $account['uuid'],
                    'provider' => $account['provider'],
                    'username' => $account['username'],
                    'nickname' => $account['nickname'],
                    'avatar' => $account['avatar'],
                    'last_seen_at' => $accounts->max('last_seen_at'),
                    'usuarios_online' => $accounts->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function visitorKey(string $visitorId): string
    {
        return self::VISITOR_KEY_PREFIX.$visitorId;
    }
}
