<?php

namespace App\Http\Controllers\Private\Pages;

use App\Http\Controllers\Controller;

use App\Http\Resources\User\UserResource;

use App\Models\User;

use Inertia\Inertia;

class ProfilePageController extends Controller
{
    private $render = 'private/Profile';

    public function render(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::render($this->render, [
            'profile' => $this->indexProfile($user),
        ]);
    }

    private function indexProfile(User $user): UserResource
    {
        return new UserResource($user->load($this->profileRelations()));
    }

    private function profileRelations(): array
    {
        return [
            'favorites',
            'socials',
            'preferences',
            'roles' => fn ($query) => $query
                ->withCount('members')
                ->with('permissions'),
        ];
    }
}
