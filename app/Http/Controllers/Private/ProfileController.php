<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Models\User;

use App\Http\Resources\UserResource;

use App\Actions\Profile\UpdateProfileAction;
use App\Http\Requests\Web\Profile\UpdateProfileRequest;

class ProfileController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Profile';

    /*
     * ======================
     * PROFILE
     * ======================
     */

    public function updateProfile(UpdateProfileRequest $request, User $user, UpdateProfileAction $updateProfileAction)
    {

        $updateProfileAction->execute(
            $user,
            $request->validated(),
            $request->file('avatar')
        );

        return $this->flashMessage('update');
    }

    /*
     * ======================
     * RENDER
     * ======================
     */

    public function render(User $user)
    {
        if (request()->user()->cannot('view', $user)) {
            abort(403, 'Não autorizado.');
        }

        return Inertia::render($this->render, [
            'profile' => new UserResource($user->load(['favorites', 'socials', 'preferences'])),
        ]);
    }
}
