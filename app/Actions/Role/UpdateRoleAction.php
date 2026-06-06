<?php

namespace App\Actions\Administration\Role;

use Illuminate\Support\Facades\DB;

use App\Models\Role;
use App\Models\Permission;

class UpdateRoleAction
{
    public function execute(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $role->fill([
                'label' => $data['label'],
                'weight' => $data['weight'],
                'description' => $data['description'],
            ]);

            if ($role->isDirty()) {
                $role->save();
            }

            if (!empty($data['permissions'])) {
                $permissions = Permission::query()
                    ->whereIn('uuid', $data['permissions'])
                    ->pluck('id')
                    ->toArray();

                $role->permissions()->sync($permissions);
            }

            return $role;
        });
    }
}
