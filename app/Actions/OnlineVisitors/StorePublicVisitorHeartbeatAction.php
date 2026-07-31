<?php

namespace App\Actions\OnlineVisitors;

use App\Models\OAuthAccount;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class StorePublicVisitorHeartbeatAction
{
    private const INDEX_KEY = 'online_visitors:index';

    private const VISITOR_KEY_PREFIX = 'online_visitors:';

    private const TTL_SECONDS = 90;

    public function execute(string $visitorId, ?string $path, bool $isListening, ?OAuthAccount $oauthAccount): array
    {
        $visitorId = Str::limit($visitorId, 100, '');

        $payload = [
            'visitor_id' => $visitorId,
            'path' => $path,
            'is_listening' => $isListening,
            'last_seen_at' => now()->toISOString(),
            'oauth_account' => $oauthAccount ? [
                'id' => $oauthAccount->id,
                'uuid' => $oauthAccount->uuid,
                'provider' => $oauthAccount->provider,
                'username' => $oauthAccount->username,
                'nickname' => $oauthAccount->nickname,
                'avatar' => $oauthAccount->avatar,
            ] : null,
        ];

        Cache::put($this->visitorKey($visitorId), $payload, now()->addSeconds(self::TTL_SECONDS));
        $this->rememberVisitor($visitorId);

        return $payload;
    }

    private function rememberVisitor(string $visitorId): void
    {
        $visitorIds = collect(Cache::get(self::INDEX_KEY, []))
            ->push($visitorId)
            ->unique()
            ->values()
            ->all();

        Cache::put(self::INDEX_KEY, $visitorIds, now()->addDay());
    }

    private function visitorKey(string $visitorId): string
    {
        return self::VISITOR_KEY_PREFIX.$visitorId;
    }
}
