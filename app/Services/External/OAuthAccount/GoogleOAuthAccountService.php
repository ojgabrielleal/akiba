<?php

namespace App\Services\External\OAuthAccount;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleOAuthAccountService
{
    private const AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USER_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';

    public function authorizationUrl(): string
    {
        $state = Str::random(40);
        session(['google_auth_state' => $state]);

        return self::AUTHORIZE_URL . '?' . http_build_query([
            'client_id' => config('services.google.oauth.client_id'),
            'redirect_uri' => config('services.google.oauth.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'prompt' => 'select_account',
            'state' => $state,
        ]);
    }

    public function exchangeCodeForToken(Request $request): array
    {
        abort_unless(
            $request->filled('code') &&
            $request->filled('state') &&
            $request->string('state')->toString() === session('google_auth_state'), 403,
        );

        session()->forget('google_auth_state');

        return $this->google()
            ->asForm()
            ->post(self::TOKEN_URL, [
                'client_id' => config('services.google.oauth.client_id'),
                'client_secret' => config('services.google.oauth.client_secret'),
                'grant_type' => 'authorization_code',
                'code' => $request->string('code')->toString(),
                'redirect_uri' => config('services.google.oauth.redirect_uri'),
            ])
            ->throw()
            ->json();
    }

    public function getUser(string $accessToken): array
    {
        return $this->google()
            ->withToken($accessToken)
            ->get(self::USER_URL)
            ->throw()
            ->json();
    }

    private function google(): PendingRequest
    {
        return Http::acceptJson();
    }
}
