<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\User;

use App\Http\Resources\UserResource;

class ProfilePageController extends Controller
{
    private $render = 'private/Profile';

    public function render(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::render($this->render, [
            'profile' => new UserResource($user->load(['favorites', 'socials', 'preferences'])),
        ]);
    }
}
