<?php

namespace App\Http\Controllers\Api\External\OAuthAccount;

use App\Http\Controllers\Controller;
use App\Services\OAuthAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class OAuthAccountCallbackController extends Controller
{
    public function __invoke(Request $request, OAuthAccountService $service, string $provider): RedirectResponse
    {
        abort_unless($this->isSupportedProvider($provider), 404);

        try {
            $service->storeFromProvider($provider, Socialite::driver($provider)->stateless()->user(), $request);
        } catch (Throwable $exception) {
            Log::warning('OAuth callback failed', [
                'provider' => $provider,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('home')->with('flash', [
                'id' => uniqid('flash_', true),
                'type' => 'error',
                'icon' => '❌',
                'message' => 'Não foi possível concluir o login. Tente novamente em instantes.',
            ]);
        }

        return redirect()->route('home');
    }

    private function isSupportedProvider(string $provider): bool
    {
        return in_array($provider, ['discord', 'google'], true);
    }
}
