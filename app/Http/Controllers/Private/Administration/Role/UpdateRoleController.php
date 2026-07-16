<?php

namespace App\Http\Controllers\Private\Administration\Role;

use App\Actions\Role\UpdateRoleAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;

class UpdateRoleController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateRoleRequest $request, Role $role, UpdateRoleAction $updateRoleAction)
    {
        $updateRoleAction->execute($role, $request->validated());

        return $this->flashMessage('update');
    }
}
