<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function render(Request $request)
    {
        $redirect = $request->query('redirect', '/');
        $redirect = is_string($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')
            ? $redirect
            : '/';

        return Inertia::render('public/AuthContinue', [
            'authContext' => [
                'reason' => $request->query('reason', 'default'),
                'redirect' => $redirect,
            ],
        ]);
    }
}
