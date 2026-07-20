<?php

namespace App\Actions\OAuthAccount\Providers;

use App\Models\OAuthAccount;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiscordOAuthAccountAction
{
    public function execute(array $discordUser, Request $request): OAuthAccount
    {
        $accountToken = Str::random(64);

        $oauth = DB::transaction(function () use ($discordUser, $accountToken) {
            $oauth = OAuthAccount::query()
                ->where('provider', 'discord')
                ->where('provider_user_id', $discordUser['id'])
                ->firstOrNew();

            $oauth->fill([
                'provider' => 'discord',
                'provider_user_id' => $discordUser['id'],
                'username' => $discordUser['username'] ?? null,
                'nickname' => $discordUser['global_name'] ?? $discordUser['username'] ?? null,
                'avatar' => isset($discordUser['avatar']) ? "https://cdn.discordapp.com/avatars/{$discordUser['id']}/{$discordUser['avatar']}.webp?size=256" : null,
                'account_token_hash' => hash('sha256', $accountToken),
            ]);
            $oauth->save();

            return $oauth;
        });

        Cookie::queue(
            Cookie::make(
                'akiba_oauth_token',
                $accountToken,
                60 * 24 * 30,
                null,
                null,
                $request->isSecure(),
                true,
                false,
                'lax',
            )
        );

        return $oauth;
    }
}
