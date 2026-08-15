<?php

namespace App\Services;

use App\Models\OAuthAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;

class OAuthAccountService
{
    public function update(OAuthAccount $oauthAccount, array $data): OAuthAccount
    {
        return DB::transaction(function () use ($oauthAccount, $data) {
            $oauthAccount->update([
                'nickname' => $data['nickname'],
                'birth_date' => $data['birth_date'],
                'address' => $data['address'],
                'profile_completed_at' => now(),
            ]);

            return $oauthAccount;
        });
    }

    public function storeFromProvider(string $provider, ProviderUser $providerUser, Request $request): OAuthAccount
    {
        $accountToken = Str::random(64);

        $oauth = DB::transaction(function () use ($provider, $providerUser, $accountToken) {
            return OAuthAccount::query()->updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_user_id' => $providerUser->getId(),
                ],
                [
                    'username' => $this->username($provider, $providerUser),
                    'nickname' => $this->nickname($provider, $providerUser),
                    'avatar' => $providerUser->getAvatar(),
                    'account_token_hash' => hash('sha256', $accountToken),
                ],
            );
        });

        $this->queueAccountTokenCookie($accountToken, $request);

        return $oauth;
    }

    private function username(string $provider, ProviderUser $providerUser): ?string
    {
        return match ($provider) {
            'google' => $providerUser->getEmail(),
            default => $providerUser->getNickname(),
        };
    }

    private function nickname(string $provider, ProviderUser $providerUser): ?string
    {
        return match ($provider) {
            'google' => $providerUser->getName() ?? $providerUser->getNickname() ?? $providerUser->getEmail(),
            default => $providerUser->getName() ?? $providerUser->getNickname(),
        };
    }

    private function queueAccountTokenCookie(string $accountToken, Request $request): void
    {
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
    }
}
