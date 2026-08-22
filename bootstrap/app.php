<?php

use App\Http\Resources\Onair\OnairResource;
use App\Http\Middleware\OAuth\ResolveOAuthAccount;
use App\Integrations\StreamService;
use App\Services\OnairService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            //
        ]);

        $middleware->alias([
            'inertia' => \App\Http\Middleware\HandleInertiaRequestsMiddleware::class,
            'oauth.resolve' => \App\Http\Middleware\OAuth\ResolveOAuthAccount::class,
            'oauth' => \App\Http\Middleware\OAuth\EnsureOAuthAccountAuthenticated::class,
            'authenticated.user' => \App\Http\Middleware\Auth\ShareAuthenticatedUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Not Found'], 404);
            }

            if ($request->is('panel') || $request->is('panel/*')) {
                return null;
            }

            return Inertia::render('public/NotFound', [
                'oauth' => fn () => ResolveOAuthAccount::resolve($request),
                'onair' => fn () => OnairResource::collection(
                    app(OnairService::class)->filter([
                        'live' => true,
                        'with' => 'program.host',
                    ])
                ),
                'stream' => fn () => (new StreamService)->data(),
                'flash' => fn () => session('flash'),
            ])->toResponse($request)->setStatusCode(404);
        });
    })->create();
