<?php

namespace App\Actions\OAuth;

use App\Models\OAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiscordOAuthAction
{
    public function execute(array $discordUser, Request $request): OAuth
    {
        $accountToken = Str::random(64);

        $oauth = DB::transaction(function () use ($discordUser, $accountToken) {
            $oauth = OAuth::query()
                ->where('provider->name', 'discord')
                ->where('provider->user_id', $discordUser['id'])
                ->firstOrNew();

            $oauth->fill([
                'provider' => $this->provider($discordUser),
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

    private function provider(array $discordUser): array
    {
        return [
            'name' => 'discord',
            'user_id' => $discordUser['id'],
            'username' => $discordUser['username'] ?? null,
            'global_name' => $discordUser['global_name'] ?? null,
            'avatar' => $discordUser['avatar'] ?? null,
        ];
    }
}
