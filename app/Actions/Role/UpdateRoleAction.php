<?php

namespace App\Actions\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Services\Process\ImageProcessService;

use Illuminate\Support\Facades\DB;

class UpdateRoleAction
{
    public function __construct(
        private ImageProcessService $image
    )
    {}

    public function execute(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $role->fill([
                'label' => $data['label'],
                'weight' => $data['weight'],
                'description' => $data['description'],
                'icon' => $this->image->store('roles', $data['icon'] ?? null, $role->icon),
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
