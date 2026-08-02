<?php

namespace App\Http\Middleware\Auth;

use App\Services\Process\PublicVisitorPresenceService;
use Closure;
use Illuminate\Http\Request;

use Inertia\Inertia;

use Symfony\Component\HttpFoundation\Response;

class ShareAuthenticatedUserMiddleware
{
    public function __construct(private PublicVisitorPresenceService $publicVisitorPresence) {}

    public function handle(Request $request, Closure $next): Response
    {
        Inertia::share('user', function () use ($request) {
            $user = $request->user()->load('roles.permissions');

            return [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'slug' => $user->slug,
                'name' => $user->name,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'gender' => $user->gender,
                'roles' => $user->roles,
                'permissions' => $user->roles
                    ->flatMap(fn ($role) => $role->permissions)
                    ->pluck('name')
                    ->unique()
                    ->values(),
            ];
        });

        Inertia::share('publicVisitors', fn () => $this->publicVisitorPresence->summary());

        return $next($request);
    }
}
