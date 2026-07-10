<?php

namespace App\Http\Controllers\Private\Administration\Role;

use App\Exceptions\RoleHasMembersException;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Role;

class DestroyRoleController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Role $role)
    {
        $this->authorize('delete', $role);

        if ($role->members()->count() > 0) {
            throw new RoleHasMembersException;
        }

        $role->delete();

        return $this->flashMessage('delete');
    }
}
