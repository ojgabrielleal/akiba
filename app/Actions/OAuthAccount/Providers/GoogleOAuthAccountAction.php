<?php

namespace App\Actions\OAuthAccount\Providers;

use App\Models\OAuthAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoogleOAuthAccountAction
{
    public function execute(array $googleUser, Request $request): OAuthAccount
    {
        $accountToken = Str::random(64);

        $oauth = DB::transaction(function () use ($googleUser, $accountToken) {
            return OAuthAccount::query()->updateOrCreate(
                [
                    'provider' => 'google',
                    'provider_user_id' => $googleUser['id'],
                ],
                [
                    'username' => $googleUser['email'] ?? null,
                    'nickname' => $googleUser['name'] ?? $googleUser['given_name'] ?? $googleUser['email'] ?? null,
                    'avatar' => $googleUser['picture'] ?? null,
                    'account_token_hash' => hash('sha256', $accountToken),
                ],
            );
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
