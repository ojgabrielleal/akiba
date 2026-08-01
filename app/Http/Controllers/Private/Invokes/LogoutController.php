<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
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
}
