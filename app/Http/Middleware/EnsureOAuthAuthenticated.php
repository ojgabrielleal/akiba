<?php

namespace App\Http\Middleware;

use App\Models\OAuth;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureOAuthAuthenticated
{
    public function handle(Request $request, Closure $next, string $provider = 'discord'): Response
    {
        $oauthToken = $request->cookie('akiba_oauth_token');
        if (!$oauthToken) return Inertia::location(route('oauth.redirect', ['provider' => $provider]));

        $oauth = OAuth::where('account_token_hash', hash('sha256', $oauthToken))->first();
        if(!$oauth) return Inertia::location(route('oauth.redirect', ['provider' => $provider]));

        $request->attributes->set('oauth', $oauth);
        return $next($request);
    }
}
