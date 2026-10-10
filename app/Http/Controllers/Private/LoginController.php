<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;

use Inertia\Inertia;
use App\Http\Requests\Login\AuthLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Services\InternalBrowserRecognitionService;

class LoginController extends Controller
{
    private $render = 'private/Login';

    public function loginUser(AuthLoginRequest $request, InternalBrowserRecognitionService $browserRecognition)
    {
        $request->ensureIsNotRateLimited();

        $data = $request->validated();
        $credentials = Arr::only($data, ['username', 'password']);
        $credentials['is_active'] = true;
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $request->clearRateLimiter();
            $browserRecognition->remember($request->user());

            return redirect()->intended(route('panel.dashboard'));
        }

        $request->hitRateLimiter();

        return Inertia::render($this->render)->with('flash', [
            'type' => 'error',
            'icon' => '😠',
            'message' => 'Usuário ou senha incorretos',
        ]);
    }

    public function logoutUser(Request $request, InternalBrowserRecognitionService $browserRecognition)
    {
        $browserRecognition->forget($request);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function authenticateRecognizedBrowser(Request $request, InternalBrowserRecognitionService $browserRecognition)
    {
        $user = $browserRecognition->resolveUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $browserRecognition->remember($user);

        $redirect = (string) $request->query('redirect', route('panel.dashboard', absolute: false));
        if (! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = route('panel.dashboard', absolute: false);
        }

        return redirect()->to($redirect);
    }

    public function render()
    {
        return Inertia::render($this->render);
    }
}
