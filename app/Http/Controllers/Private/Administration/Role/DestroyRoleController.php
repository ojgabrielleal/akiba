<?php

namespace App\Http\Controllers\Private\Administration\Role;

use App\Actions\Role\DestroyRoleAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Role;

class DestroyRoleController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Role $role, DestroyRoleAction $destroyRoleAction)
    {
        $this->authorize('delete', $role);

        $destroyRoleAction->execute($role);

        return $this->flashMessage('delete');
    }
}
