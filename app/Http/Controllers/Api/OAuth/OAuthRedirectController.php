<?php

namespace App\Http\Controllers\Api\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class OAuthRedirectController extends Controller
{
    public function __invoke(string $provider): RedirectResponse
    {
        $oauthProvider = config("oauth.providers.$provider");
        abort_unless($oauthProvider, 404);

        return redirect()->away(app($oauthProvider['service'])->authorizationUrl());
    }
}
