<?php

namespace App\Http\Controllers\Api\External\OAuthAccount;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class OAuthAccountRedirectController extends Controller
{
    public function __invoke(string $provider): RedirectResponse
    {
        abort_unless($this->isSupportedProvider($provider), 404);
        return Socialite::driver($provider)->redirect();
    }

    private function isSupportedProvider(string $provider): bool
    {
        return in_array($provider, ['discord', 'google'], true);
    }
}
