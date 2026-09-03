<?php

namespace App\Services;

use App\Exceptions\RoleHasMembersException;
use App\Models\Role;
use App\Processing\ImageProcess;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleService
{
    public function __construct(
        private ImageProcess $image,
        private CacheService $cache,
    ) {}

    public function destroy(Role $role): void
    {
        DB::transaction(function () use ($role) {
            if ($role->members()->count() > 0) {
                throw new RoleHasMembersException;
            }

            $icon = $role->icon;

            $role->delete();
            $this->image->delete($icon);
        });

        $this->cache->invalidateRoles();
    }

    public function store(array $data): Role
    {
        $role = DB::transaction(function () use ($data) {
            $role = $this->storeStoreRole($data);
            $this->storeSyncPermissions($role, $data['permissions'] ?? []);

            return $role;
        });

        $this->cache->invalidateRoles();

        return $role;
    }

    private function storeStoreRole(array $data): Role
    {
        return Role::create([
            'label' => $data['label'],
            'public_label' => filled($data['public_label'] ?? null) ? $data['public_label'] : $data['label'],
            'weight' => $data['weight'],
            'description' => $data['description'],
            'icon' => $this->image->store('roles', $data['icon']),
        ]);
    }

    private function storeSyncPermissions(Role $role, array $permissionUuids): void
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

    public function update(Role $role, array $data): Role
    {
        $role = DB::transaction(function () use ($role, $data) {
            $this->updateUpdateRole($role, $data);
            $this->updateSyncPermissions($role, $data['permissions'] ?? []);

            return $role;
        });

        $this->cache->invalidateRoles();

        return $role;
    }

    private function updateUpdateRole(Role $role, array $data): void
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

    private function updateSyncPermissions(Role $role, array $permissionUuids): void
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

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Role::query()
            ->when(
                $filters['with_count'] ?? null,
                fn (Builder $query, array|string $relations) => $query->withCount($relations)
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }}
