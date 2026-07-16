<?php

namespace App\Http\Controllers\Private\Administration\User;

use App\Actions\User\StoreUserAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;

class StoreUserController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StoreUserRequest $request, StoreUserAction $storeUserAction)
    {
        $storeUserAction->execute($request->validated());

        return $this->flashMessage('save');
    }
}
