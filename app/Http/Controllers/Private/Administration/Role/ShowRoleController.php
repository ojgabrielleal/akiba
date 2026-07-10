<?php

namespace App\Http\Controllers\Private\Administration\Role;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;

class ShowRoleController extends Controller
{
    public function __invoke(Role $role)
    {
        $this->authorize('view', $role);

        return new RoleResource($role);
    }
}
