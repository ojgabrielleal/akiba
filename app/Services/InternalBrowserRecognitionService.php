<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Laravel\Sanctum\PersonalAccessToken;

class InternalBrowserRecognitionService
{
    private const COOKIE_NAME = 'akiba_internal_browser';
    private const TOKEN_NAME = 'internal-browser-recognition';
    private const TOKEN_ABILITY = 'internal-browser';
    private const EXPIRATION_DAYS = 30;

    public function remember(User $user): void
    {
        $this->revoke($this->tokenFromCookie(request()));

        $token = $user->createToken(
            self::TOKEN_NAME,
            [self::TOKEN_ABILITY],
            now()->addDays(self::EXPIRATION_DAYS),
        )->plainTextToken;

        Cookie::queue(cookie(
            self::COOKIE_NAME,
            $token,
            self::EXPIRATION_DAYS * 24 * 60,
            config('session.path'),
            config('session.domain'),
            (bool) config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax'),
        ));
    }

    public function forget(Request $request): void
    {
        $this->revoke($this->tokenFromCookie($request));

        Cookie::queue(Cookie::forget(
            self::COOKIE_NAME,
            config('session.path'),
            config('session.domain'),
        ));
    }

    public function resolveUser(Request $request): ?User
    {
        $token = $this->tokenFromCookie($request);

        if (! $token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        $user = $accessToken?->tokenable;

        if (! $user instanceof User) {
            $this->forget($request);
            return null;
        }

        if (! $accessToken->can(self::TOKEN_ABILITY) || $accessToken->name !== self::TOKEN_NAME) {
            $this->forget($request);
            return null;
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            $this->forget($request);
            return null;
        }

        if (! $user->is_active) {
            $this->forget($request);
            return null;
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();

        return $user;
    }

    public function revoke(?string $token): void
    {
        if (! $token) {
            return;
        }

        PersonalAccessToken::findToken($token)?->delete();
    }

    private function tokenFromCookie(Request $request): ?string
    {
        return $request->cookie(self::COOKIE_NAME);
    }
}
