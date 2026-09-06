<?php

namespace App\Http\Controllers\Api\External\OAuthAccount;

use App\Http\Controllers\Controller;
use App\Models\OAuthAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Laravel\Sanctum\PersonalAccessToken;

class OAuthAccountLogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $accessToken = PersonalAccessToken::findToken((string) $request->cookie('akiba_oauth_token'));

        if ($accessToken?->tokenable instanceof OAuthAccount) {
            $accessToken->delete();
        }

        Cookie::queue(Cookie::forget('akiba_oauth_token'));

        return redirect()->route('home');
    }
}
