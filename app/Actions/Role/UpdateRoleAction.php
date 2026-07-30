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
            $this->updateRole($role, $data);
            $this->syncPermissions($role, $data['permissions'] ?? []);

            return $role;
        });
    }

    private function updateRole(Role $role, array $data): void
    {
        $role->fill([
            'label' => $data['label'],
            'public_label' => filled($data['public_label'] ?? null) ? $data['public_label'] : $data['label'],
            'weight' => $data['weight'],
            'description' => $data['description'],
            'icon' => $this->image->store('roles', $data['icon'] ?? null, $role->icon),
        ]);

        if ($role->isDirty()) {
            $role->save();
        }
    }

    private function syncPermissions(Role $role, array $permissionUuids): void
    {
        if (empty($permissionUuids)) {
            return;
        }

        $permissions = Permission::query()
            ->whereIn('uuid', $permissionUuids)
            ->pluck('id')
            ->toArray();

        $role->permissions()->sync($permissions);
    }
}
