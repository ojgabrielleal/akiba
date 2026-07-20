<?php

namespace App\Http\Controllers\Api\External\OAuthAccount;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OAuthAccountCallbackController extends Controller
{
    public function __invoke(Request $request, string $provider): Response
    {
        $oauthProvider = config("oauth.providers.$provider");
        abort_unless($oauthProvider, 404);

        $service = app($oauthProvider['service']);
        $tokens = $service->exchangeCodeForToken($request);
        $getUser = $service->getUser($tokens['access_token']);

        app($oauthProvider['action'])->execute($getUser, $request);

        $payload = json_encode([
            'type' => 'authenticated',
        ], JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return response(<<<HTML
            <!doctype html>
            <html lang="pt-BR">
                <head>
                    <meta charset="utf-8">
                    <title>Autenticação concluída</title>
                </head>
                <body>
                    <p>Autenticação concluída. Você já pode fechar esta aba.</p>
                    <script>
                        const channel = new BroadcastChannel("akiba_oauth");
                        channel.postMessage({$payload});
                        channel.close();
                        window.close();
                    </script>
                </body>
            </html>
            HTML)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
