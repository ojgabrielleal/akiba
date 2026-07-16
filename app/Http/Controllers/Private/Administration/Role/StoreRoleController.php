<?php

namespace App\Http\Controllers\Private\Administration\Role;

use App\Actions\Role\StoreRoleAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;

class StoreRoleController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StoreRoleRequest $request, StoreRoleAction $storeRoleAction)
    {
        $storeRoleAction->execute($request->validated());

        return $this->flashMessage('save');
    }
}
