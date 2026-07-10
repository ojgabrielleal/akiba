<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Activity;
use App\Models\Calendar;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\CalendarResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;

class AdministrationPageController extends Controller
{
    private $render = 'private/Administration';

    public function render()
    {
        return Inertia::render($this->render, [
            'roles' => $this->indexRoles(),
            'permissions' => $this->indexPermissions(),
            'activities' => $this->indexActivities(),
            'calendar' => $this->indexCalendar(),
            'users' => $this->indexUsers(),
            'tasks' => $this->indexTasks(),
        ]);
    }

    public function indexActivities()
    {
        $this->authorize('viewAny', Activity::class);

        return ActivityResource::collection(
            Activity::valid()->with(['author', 'confirmations'])->latest()->get()
        );
    }

    public function indexTasks()
    {
        $this->authorize('viewAny', Task::class);

        return TaskResource::collection(Task::incompleted()->get());
    }

    public function indexCalendar()
    {
        $this->authorize('viewAny', Calendar::class);

        return CalendarResource::collection(Calendar::valid()->get());
    }

    public function indexUsers()
    {
        $this->authorize('viewAny', User::class);

        return UserResource::collection(User::active()->with(['roles'])->get())->format('summary');
    }

    public function indexRoles()
    {
        $this->authorize('viewAny', Role::class);

        return RoleResource::collection(Role::with(['permissions', 'members'])->get());
    }

    public function indexPermissions()
    {
        $this->authorize('viewAny', Role::class);

        return PermissionResource::collection(Permission::all());
    }
}
