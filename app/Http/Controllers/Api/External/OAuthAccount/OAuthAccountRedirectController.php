<?php

namespace App\Http\Controllers\Api\External\OAuthAccount;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OAuthAccountRedirectController extends Controller
{
    public function __invoke(Request $request, string $provider): RedirectResponse
    {
        abort_unless($this->isSupportedProvider($provider), 404);

        $redirect = $request->query('redirect');

        if (is_string($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            $request->session()->put('oauth_redirect_after_login', $redirect);
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    private function isSupportedProvider(string $provider): bool
    {
        return in_array($provider, ['discord', 'google'], true);
    }
}
