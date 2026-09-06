<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;

use Inertia\Inertia;
use App\Http\Requests\Login\AuthLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    private $render = 'private/Login';

    public function loginUser(AuthLoginRequest $request)
    {
        $request->ensureIsNotRateLimited();

        $data = $request->validated();
        $credentials = Arr::only($data, ['username', 'password']);
        $credentials['is_active'] = true;
        $remember = (bool) ($data['remember'] ?? false);

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $request->clearRateLimiter();

            return redirect()->intended(route('panel.dashboard'));
        }

        $request->hitRateLimiter();

        return Inertia::render($this->render)->with('flash', [
            'type' => 'error',
            'icon' => '😠',
            'message' => 'Usuário ou senha incorretos',
        ]);
    }

    public function logoutUser(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        return Inertia::render($this->render);
    }
}
