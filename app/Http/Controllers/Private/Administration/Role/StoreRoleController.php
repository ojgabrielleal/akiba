<?php

namespace App\Http\Controllers\Private\Administration\Role;

use App\Actions\Administration\Role\CreateRoleAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Administration\CreateRoleRequest;

class StoreRoleController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreateRoleRequest $request, CreateRoleAction $createRoleAction)
    {
        $createRoleAction->execute($request->validated());

        return $this->flashMessage('save');
    }
}
