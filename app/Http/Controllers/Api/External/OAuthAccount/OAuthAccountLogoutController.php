<?php

namespace App\Http\Controllers\Api\External\OAuthAccount;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

class OAuthAccountLogoutController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        Cookie::queue(Cookie::forget('akiba_oauth_token'));

        return redirect()->route('home');
    }
}
