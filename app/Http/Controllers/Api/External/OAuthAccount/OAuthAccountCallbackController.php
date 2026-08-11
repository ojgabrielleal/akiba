<?php

namespace App\Http\Controllers\Api\External\OAuthAccount;

use App\Http\Controllers\Controller;
use App\Services\OAuthAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OAuthAccountCallbackController extends Controller
{
    public function __invoke(Request $request, OAuthAccountService $service, string $provider): RedirectResponse
    {
        abort_unless($this->isSupportedProvider($provider), 404);

        $service->storeFromProvider($provider, Socialite::driver($provider)->user(), $request);
        return redirect()->route('home');
    }

    private function isSupportedProvider(string $provider): bool
    {
        return in_array($provider, ['discord', 'google'], true);
    }
}
