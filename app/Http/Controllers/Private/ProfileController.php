<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;

use App\Http\Resources\User\UserResource;

use App\Models\User;

use Inertia\Inertia;
use App\Services\ProfileService;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\Profile\UpdateProfileRequest;

class ProfileController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Profile';

    private function indexProfile(User $user): UserResource
    {
        return new UserResource($user->load([
            'favorites',
            'topAnimes',
            'socials',
            'preferences',
            'roles' => fn ($query) => $query
                ->withCount('members')
                ->with('permissions'),
        ]));
    }

    public function updateProfile(UpdateProfileRequest $request, ProfileService $service, User $user)
    {
        $service->update($user, $request->validated(), $request->file('avatar'));

        return $this->flashMessage('update');
    }

    public function render(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::render($this->render, [
            'profile' => $this->indexProfile($user),
        ]);
    }
}
