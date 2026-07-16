<?php

namespace App\Http\Controllers\Private\Administration\User;

use App\Actions\User\UpdateUserAccessAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserAccessRequest;
use App\Models\User;

class UpdateUserAccessController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateUserAccessRequest $request, User $user, UpdateUserAccessAction $updateUserAccessAction)
    {
        $updateUserAccessAction->execute($user, $request->validated());

        return $this->flashMessage('save');
    }
}
