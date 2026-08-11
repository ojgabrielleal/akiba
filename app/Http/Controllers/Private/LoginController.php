<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;

use Inertia\Inertia;
use App\Http\Requests\Login\AuthLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private $render = 'private/Login';

    public function loginUser(AuthLoginRequest $request)
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

    public function logoutUser(Request $request)
    {
        $request->user()?->update([
            'account_token_hash' => null,
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Cookie::queue(Cookie::forget('akiba_user_token'));

        return redirect()->route('login');
    }

    public function render()
    {
        return Inertia::render($this->render);
    }
}
