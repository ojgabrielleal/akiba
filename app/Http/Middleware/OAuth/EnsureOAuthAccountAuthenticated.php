<?php

namespace App\Http\Middleware\OAuth;

use Closure;
use Illuminate\Http\Request;

use Inertia\Inertia;

use Symfony\Component\HttpFoundation\Response;

class EnsureOAuthAccountAuthenticated
{
    public function handle(Request $request, Closure $next, string $provider = 'discord'): Response
    {
        if (!$request->attributes->get('oauth_account')) {
            return Inertia::location(route('oauth.redirect', ['provider' => $provider]));
        }

        return $next($request);
    }
}
