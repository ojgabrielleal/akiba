<?php

namespace App\Http\Controllers\Private;

use App\Actions\Role\DestroyRoleAction;
use App\Actions\Role\StoreRoleAction;
use App\Actions\Role\UpdateRoleAction;

use App\Exceptions\RoleHasMembersException;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;

use App\Http\Resources\RoleResource;

use App\Models\Role;

class RoleController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private DestroyRoleAction $destroyRoleAction,
        private StoreRoleAction $storeRoleAction,
        private UpdateRoleAction $updateRoleAction,
    ) {}

    public function show(Role $role)
    {
        $this->authorize('view', $role);

        return new RoleResource(
            $role->loadCount('members')->load('permissions')
        );
    }

    public function store(StoreRoleRequest $request)
    {
        $this->storeRoleAction->execute($request->validated());

        return $this->flashMessage('save');
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->updateRoleAction->execute($role, $request->validated());

        return $this->flashMessage('update');
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        try {
            $this->destroyRoleAction->execute($role);
        } catch (RoleHasMembersException $exception) {
            return $this->flashMessage('error', $exception->getMessage(), '⛓️');
        }

        return $this->flashMessage('delete');
    }
}
