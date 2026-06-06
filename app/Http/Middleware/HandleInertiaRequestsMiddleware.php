<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Concerns\ResolvesUserLogged;
use App\Services\External\StreamService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequestsMiddleware extends Middleware
{
    use ResolvesUserLogged;

    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'user' => $this->getUserLogged(),
            'stream' => (new StreamService)->data(),
            'csrf_token' => csrf_token(),
            'flash' => session('flash'),
        ]);
    }
}
