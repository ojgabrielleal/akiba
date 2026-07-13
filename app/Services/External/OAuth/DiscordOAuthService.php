<?php

namespace App\Services\External\OAuth;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscordOAuthService
{
    private const AUTHORIZE_URL = 'https://discord.com/oauth2/authorize';
    private const TOKEN_URL = 'https://discord.com/api/oauth2/token';
    private const USER_URL = 'https://discord.com/api/users/@me';

    public function authorizationUrl(): string
    {
        $state = Str::random(40);
        session(['discord_auth_state' => $state]);

        return self::AUTHORIZE_URL . '?' . http_build_query([
            'client_id' => config('services.discord.oauth.client_id'),
            'redirect_uri' => config('services.discord.oauth.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'identify',
            'state' => $state,
        ]);
    }

    public function exchangeCodeForToken(Request $request): array
    {
        abort_unless(
            $request->filled('code') &&
            $request->filled('state') &&
            $request->string('state')->toString() === session('discord_auth_state'), 403,
        );

        session()->forget('discord_auth_state');

        return $this->discord()
            ->asForm()
            ->post(self::TOKEN_URL, [
                'client_id' => config('services.discord.oauth.client_id'),
                'client_secret' => config('services.discord.oauth.client_secret'),
                'grant_type' => 'authorization_code',
                'code' => $request->string('code')->toString(),
                'redirect_uri' => config('services.discord.oauth.redirect_uri'),
            ])
            ->throw()
            ->json();
    }

    public function getUser(string $accessToken): array
    {
        return $this->discord()
            ->withToken($accessToken)
            ->get(self::USER_URL)
            ->throw()
            ->json();
    }

    private function discord(): PendingRequest
    {
        return Http::acceptJson();
    }
}
