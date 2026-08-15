<?php

namespace App\Http\Middleware\OAuth;

use App\Models\OAuthAccount;
use App\Models\User;

use Closure;
use Illuminate\Http\Request;

use Inertia\Inertia;

use Symfony\Component\HttpFoundation\Response;

class ResolveOAuthAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $oauthToken = $request->cookie('akiba_oauth_token');
        $userToken = $request->cookie('akiba_user_token');

        $authenticatedUser = $request->user();
        $user = $authenticatedUser;
        $oauthAccount = null;

        if ($oauthToken) {
            $oauthAccount = OAuthAccount::query()
                ->where('account_token_hash', hash('sha256', $oauthToken))
                ->first();

            if ($oauthAccount) $request->attributes->set('oauth_account', $oauthAccount);
        }

        if (!$user && $userToken) {
            $user = User::query()
                ->where('account_token_hash', hash('sha256', $userToken))
                ->first();

            if ($user) $request->attributes->set('member_user', $user);
        }

        Inertia::share('oauth', [
            'type' => $user ? 'member' : ($oauthAccount ? 'oauth' : null),
            'authenticated' => $user !== null || $oauthAccount instanceof OAuthAccount,
            'is_member' => $user !== null,
            'member_session_authenticated' => $authenticatedUser !== null,
            'is_oauth' => $user === null && $oauthAccount instanceof OAuthAccount,
            'profile_completed' => $user !== null || $oauthAccount?->profile_completed_at !== null,
            'can_view_profile' => $user?->hasPermission('user.view.own') ?? true,
            'can_update_profile' => $user?->hasPermission('user.update.own') ?? true,
            'profile' => $user ? [
                'uuid' => $user->uuid,
                'provider' => 'internal',
                'username' => $user->name,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'birth_date' => $user->birth_date?->format('Y-m-d'),
                'address' => collect([$user->city, $user->state])->filter()->join(' - '),
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
                'bio' => $user->bibliography,
            ] : ($oauthAccount ? [
                'uuid' => $oauthAccount->uuid,
                'provider' => $oauthAccount->provider,
                'username' => $oauthAccount->username,
                'nickname' => $oauthAccount->nickname,
                'avatar' => $oauthAccount->avatar,
                'birth_date' => $oauthAccount->birth_date?->format('Y-m-d'),
                'address' => $oauthAccount->address,
            ] : null),
        ]);

        return $next($request);
    }
}
