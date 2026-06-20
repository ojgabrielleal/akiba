<?php

namespace App\Actions\Administration\Role;

use Illuminate\Support\Facades\DB;

use App\Models\Role;
use App\Models\Permission;

class CreateRoleAction
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
