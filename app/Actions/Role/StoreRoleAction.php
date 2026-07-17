<?php

namespace App\Actions\Role;

use App\Models\Permission;
use App\Models\Role;

use Illuminate\Support\Facades\DB;

class StoreRoleAction
{
    public function execute(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'label' => $data['label'],
                'weight' => $data['weight'],
                'description' => $data['description'],
            ]);

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
