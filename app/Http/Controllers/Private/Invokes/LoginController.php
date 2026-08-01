<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Http\Controllers\Controller;

use App\Http\Requests\Login\AuthLoginRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

use Inertia\Inertia;

class LoginController extends Controller
{
    private $render = 'private/Login';

    public function __invoke(AuthLoginRequest $request)
    {
        $credentials = $request->validated();

        $credentials['is_active'] = true;

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $accountToken = Str::random(64);

            $request->user()->update([
                'account_token_hash' => hash('sha256', $accountToken),
            ]);

            Cookie::queue(
                Cookie::make(
                    'akiba_user_token',
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

            return redirect()->intended(route('panel.dashboard'));
        }

        return Inertia::render($this->render)->with('flash', [
            'type' => 'error',
            'icon' => '😠',
            'message' => 'Usuário ou senha incorretos',
        ]);
    }
}
