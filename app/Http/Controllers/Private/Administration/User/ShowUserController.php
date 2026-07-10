<?php

namespace App\Http\Controllers\Private\Administration\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;

class ShowUserController extends Controller
{
    public function __invoke(User $user)
    {
        $this->authorize('view', $user);

        return new UserResource($user->load(['roles']));
    }
}
