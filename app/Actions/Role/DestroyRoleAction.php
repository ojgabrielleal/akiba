<?php

namespace App\Actions\Role;

use App\Exceptions\RoleHasMembersException;

use App\Models\Role;
use App\Services\Process\ImageProcessService;

use Illuminate\Support\Facades\DB;

class DestroyRoleAction
{
    public function __construct(
        private ImageProcessService $image
    )
    {}

    public function execute(Role $role): void
    {
        DB::transaction(function () use ($role) {
            if ($role->members()->count() > 0) {
                throw new RoleHasMembersException;
            }

            $icon = $role->icon;

            $role->delete();
            $this->image->delete($icon);
        });
    }
}
