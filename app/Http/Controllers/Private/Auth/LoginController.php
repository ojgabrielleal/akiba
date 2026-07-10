<?php

namespace App\Http\Controllers\Private\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Login\AuthLoginRequest;
use Illuminate\Support\Facades\Auth;
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

            return redirect()->intended(route('panel.dashboard'));
        }

        return Inertia::render($this->render)->with('flash', [
            'icon' => '😠',
            'message' => 'Usuário ou senha incorretos',
        ]);
    }
}
