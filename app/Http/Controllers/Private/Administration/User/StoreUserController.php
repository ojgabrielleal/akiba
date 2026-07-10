<?php

namespace App\Http\Controllers\Private\Administration\User;

use App\Actions\Administration\User\CreateUserAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Administration\CreateUserRequest;

class StoreUserController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreateUserRequest $request, CreateUserAction $createUserAction)
    {
        $createUserAction->execute($request->validated());

        return $this->flashMessage('save');
    }
}
