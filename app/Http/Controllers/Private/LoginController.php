<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Http\Requests\Auth\AuthLoginRequest;

class LoginController extends Controller
{
    private $render = 'private/Login';

    /*
     * ======================
     * AUTHENTICATION
     * ======================
     */

    public function login(AuthLoginRequest $request)
    {
        $credentials = $request->validated();

        $credentials['is_active'] = true;

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('panel.dashboard'));
        }

        return Inertia::render($this->render)->with('flash', [
            'icon' => '😠',
            'message' => 'Usuário ou senha incorretos',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /*
     * ======================
     * RENDER
     * ======================
     */

    public function render()
    {
        return Inertia::render($this->render);
    }
}
