<?php

namespace App\Actions\Role;

use App\Exceptions\RoleHasMembersException;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class DestroyRoleAction
{
    public function execute(Role $role): void
    {
        DB::transaction(function () use ($role) {
            if ($role->members()->count() > 0) {
                throw new RoleHasMembersException;
            }

            $role->delete();
        });
    }
}
