<?php

namespace App\Http\Middleware;

use App\Filters\OnairFilter;
use App\Http\Resources\Onair\OnairResource;
use App\Services\External\StreamService;

use Illuminate\Http\Request;

use Inertia\Middleware;

class HandleInertiaRequestsMiddleware extends Middleware
{
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
            'onair' => fn () => OnairResource::collection(
                app(OnairFilter::class)->apply([
                    'live' => true,
                    'with' => 'program.host',
                ])
            ),
            'stream' => fn () => (new StreamService)->data(),
            'flash' => fn () => session('flash'),
        ]);
    }
}
