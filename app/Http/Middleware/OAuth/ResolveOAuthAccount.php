<?php

namespace App\Http\Middleware\OAuth;

use App\Models\OAuthAccount;

use Closure;
use Illuminate\Http\Request;

use Inertia\Inertia;

use Symfony\Component\HttpFoundation\Response;

class ResolveOAuthAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $oauthToken = $request->cookie('akiba_oauth_token');
        $oauthAccount = null;

        if ($oauthToken) {
            $oauthAccount = OAuthAccount::query()
                ->where('account_token_hash', hash('sha256', $oauthToken))
                ->first();

            if ($oauthAccount) {
                $request->attributes->set('oauth_account', $oauthAccount);
            }
        }

        Inertia::share('oauth', [
            'authenticated' => $oauthAccount instanceof OAuthAccount,
            'profile_completed' => $oauthAccount?->profile_completed_at !== null,
            'profile' => $oauthAccount ? [
                'uuid' => $oauthAccount->uuid,
                'provider' => $oauthAccount->provider,
                'username' => $oauthAccount->username,
                'nickname' => $oauthAccount->nickname,
                'avatar' => $oauthAccount->avatar,
                'birth_date' => $oauthAccount->birth_date?->format('Y-m-d'),
                'address' => $oauthAccount->address,
                'bio' => $oauthAccount->bio,
            ] : null,
        ]);

        return $next($request);
    }
}
