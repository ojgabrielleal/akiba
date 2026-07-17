<?php

namespace App\Http\Controllers\Private;

use App\Actions\User\StoreUserAction;
use App\Actions\User\UpdateUserAccessAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserAccessRequest;

use App\Http\Resources\User\UserResource;

use App\Models\User;

class UserController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private StoreUserAction $storeUserAction,
        private UpdateUserAccessAction $updateUserAccessAction,
    ) {}

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return new UserResource($user->load([
            'roles' => fn ($query) => $query
                ->withCount('members')
                ->with('permissions'),
        ]));
    }

    public function store(StoreUserRequest $request)
    {
        $this->storeUserAction->execute($request->validated());

        return $this->flashMessage('save');
    }

    public function updateAccess(UpdateUserAccessRequest $request, User $user)
    {
        $this->updateUserAccessAction->execute($user, $request->validated());

        return $this->flashMessage('save');
    }
}
