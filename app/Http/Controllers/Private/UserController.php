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

use Inertia\Inertia;

class UserController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Administration';

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::render($this->render, [
            'user' => $this->indexUser($user),
        ]);
    }

    private function indexUser(User $user): UserResource
    {
        return new UserResource($user->load($this->userRelations()));
    }

    private function userRelations(): array
    {
        return [
            'roles' => fn ($query) => $query
                ->withCount('members')
                ->with('permissions'),
        ];
    }

    public function store(StoreUserRequest $request, StoreUserAction $action)
    {
        $action->execute($request->validated());

        return $this->flashMessage('save');
    }

    public function updateAccess(UpdateUserAccessRequest $request, UpdateUserAccessAction $action, User $user)
    {
        $action->execute($user, $request->validated());

        return $this->flashMessage('save');
    }
}
