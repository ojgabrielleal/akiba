<?php

namespace App\Http\Controllers\Api\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OAuthCallbackController extends Controller
{
    public function __invoke(Request $request, string $provider): void
    {
        $oauthProvider = config("oauth.providers.$provider");
        abort_unless($oauthProvider, 404);

        $service = app($oauthProvider['service']);
        $tokens = $service->exchangeCodeForToken($request);
        $getUser = $service->getUser($tokens['access_token']);

        app($oauthProvider['action'])->execute($getUser, $request);
    }
}
