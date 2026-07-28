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

use Inertia\Inertia;

class RoleController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Administration';

    public function show(Role $role)
    {
        $this->authorize('view', $role);

        return Inertia::render($this->render, [
            'role' => new RoleResource(
                $role->loadCount('members')->load('permissions')
            ),
        ]);
    }

    public function store(StoreRoleRequest $request, StoreRoleAction $action)
    {
        $action->execute($request->validated());

        return $this->flashMessage('save');
    }

    public function update(UpdateRoleRequest $request, UpdateRoleAction $action, Role $role)
    {
        $action->execute($role, $request->validated());

        return $this->flashMessage('update');
    }

    public function destroy(DestroyRoleAction $action, Role $role)
    {
        $this->authorize('delete', $role);

        try {
            $action->execute($role);
        } catch (RoleHasMembersException $exception) {
            return $this->flashMessage('error', $exception->getMessage(), '⛓️');
        }

        return $this->flashMessage('delete');
    }
}
