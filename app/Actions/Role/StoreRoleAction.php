<?php

namespace App\Actions\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Services\Process\ImageProcessService;

use Illuminate\Support\Facades\DB;

class StoreRoleAction
{
    public function __construct(
        private ImageProcessService $image
    )
    {}

    public function execute(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'label' => $data['label'],
                'weight' => $data['weight'],
                'description' => $data['description'],
                'icon' => $this->image->store('roles', $data['icon']),
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
