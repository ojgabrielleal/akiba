<?php

namespace App\Http\Controllers\Private;

use App\Exceptions\RoleHasMembersException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;

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

use App\Actions\Administration\Activity\CreateActivityAction;
use App\Actions\Administration\Activity\UpdateActivityAction;
use App\Actions\Administration\Calendar\CreateCalendarAction;
use App\Actions\Administration\Calendar\UpdateCalendarAction;
use App\Actions\Administration\Role\CreateRoleAction;
use App\Actions\Administration\Role\UpdateRoleAction;
use App\Actions\Administration\Task\CreateTaskAction;
use App\Actions\Administration\Task\UpdateTaskAction;
use App\Actions\Administration\User\CreateUserAction;
use App\Actions\Administration\User\UpdateUserAccessAction;

use App\Http\Requests\Administration\CreateActivityRequest;
use App\Http\Requests\Administration\UpdateActivityRequest;
use App\Http\Requests\Administration\UpdateCalendarRequest;
use App\Http\Requests\Administration\UpdateRoleRequest;
use App\Http\Requests\Administration\UpdateTaskRequest;
use App\Http\Requests\Administration\UpdateUserAccessRequest;

class AdministrationController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Administration';

    /*
     * ======================
     * ACTIVITIES
     * ======================
     */

    public function indexActivities()
    {
        if (request()->user()->cannot('viewAny', Activity::class)) {
            return null;
        }

        return ActivityResource::collection(
            Activity::valid()->with(['author', 'confirmations'])->latest()->get()
        );
    }

    public function showActivity(Activity $activity)
    {
        if (request()->user()->cannot('view', $activity)) {
            return null;
        }

        return new ActivityResource(
            $activity->load(['author', 'confirmations', 'calendar'])
        );
    }

    public function createActivity(CreateActivityRequest $request, CreateActivityAction $createActivityAction)
    {
        if ($request->user()->cannot('create', Activity::class)) {
            return null;
        }

        $createActivityAction->execute($request->user()->id, $request->all());

        return $this->flashMessage('save');
    }

    public function updateActivity(UpdateActivityRequest $request, Activity $activity, UpdateActivityAction $updateActivityAction)
    {
        if ($request->user()->cannot('update', $activity)) {
            return null;
        }

        $updateActivityAction->execute($request->user()->id, $activity, $request->all());

        return $this->flashMessage('update');
    }

    /*
     * ======================
     * CALENDAR
     * ======================
     */

    public function indexCalendar()
    {
        if (request()->user()->cannot('viewAny', Calendar::class)) {
            return null;
        }

        return CalendarResource::collection(Calendar::valid()->get());
    }

    public function showCalendar(Calendar $calendar)
    {
        if (request()->user()->cannot('view', $calendar)) {
            return null;
        }

        return new CalendarResource($calendar->load(['activity', 'responsible']));
    }

    public function createCalendar(Request $request, CreateCalendarAction $createCalendarAction)
    {
        if (request()->user()->cannot('create', Calendar::class)) {
            return null;
        }

        $createCalendarAction->execute($request->all());

        return $this->flashMessage('save');
    }

    public function updateCalendar(UpdateCalendarRequest $request, Calendar $calendar, UpdateCalendarAction $updateCalendarAction)
    {
        if ($request->user()->cannot('update', $calendar)) {
            return null;
        }

        $updateCalendarAction->execute($calendar, $request->all());

        return $this->flashMessage('update');
    }

    /*
     * ======================
     * TASKS
     * ======================
     */

    public function indexTask()
    {
        if (request()->user()->cannot('viewAny', Task::class)) {
            return null;
        }

        return TaskResource::collection(Task::incompleted()->get());
    }

    public function showTask(Task $task)
    {
        if (request()->user()->cannot('view', $task)) {
            return null;
        }

        return new TaskResource($task->load(['responsible']));
    }

    public function createTask(Request $request, CreateTaskAction $createTaskAction)
    {
        if (request()->user()->cannot('create', Task::class)) {
            return null;
        }

        $createTaskAction->execute($request->all());

        return $this->flashMessage('save');
    }

    public function updateTask(UpdateTaskRequest $request, Task $task, UpdateTaskAction $updateTaskAction)
    {
        if (request()->user()->cannot('update', $task)) {
            return null;
        }

        $updateTaskAction->execute($task, $request->all());

        return $this->flashMessage('update');
    }

    /*
     * ======================
     * USERS
     * ======================
     */

    public function indexUsers()
    {
        if (request()->user()->cannot('viewAny', User::class)) {
            return null;
        }

        return UserResource::collection(User::active()->with(['roles'])->get())->format('summary');
    }

    public function showUser(User $user)
    {
        if (request()->user()->cannot('view', $user)) {
            return null;
        }

        return new UserResource($user->load(['roles']));
    }

    public function createUser(Request $request, CreateUserAction $createUserAction)
    {
        if ($request->user()->cannot('create', User::class)) {
            return null;
        }

        $createUserAction->execute($request->all());

        return $this->flashMessage('save');
    }

    public function updateUserAccess(UpdateUserAccessRequest $request, User $user, UpdateUserAccessAction $updateUserAccessAction)
    {
        if ($request->user()->cannot('updateAuthority', $user)) {
            return null;
        }

        $updateUserAccessAction->execute($user, $request->all());

        return $this->flashMessage('save');
    }

    public function deactivateUser(User $user)
    {
        if (request()->user()->cannot('delete', $user)) {
            return null;
        }

        $user->update(['is_active' => false]);

        return $this->flashMessage('deactivate');
    }

    /*
     * ======================
     * ROLES & PERMISSIONS
     * ======================
     */

    public function indexRole()
    {
        if (request()->user()->cannot('viewAny', Role::class)) {
            return null;
        }

        return RoleResource::collection(Role::with(['permissions', 'members'])->get());
    }

    public function showRole(Role $role)
    {
        if (request()->user()->cannot('view', $role)) {
            return null;
        }

        return new RoleResource($role);
    }

    public function indexPermissions()
    {
        if (request()->user()->cannot('viewAny', Role::class)) {
            return null;
        }

        return PermissionResource::collection(Permission::all());
    }

    public function createRole(Request $request, CreateRoleAction $createRoleAction)
    {
        if ($request->user()->cannot('create', Role::class)) {
            return null;
        }

        $createRoleAction->execute($request->all());

        return $this->flashMessage('save');
    }

    public function updateRole(UpdateRoleRequest $request, Role $role, UpdateRoleAction $updateRoleAction)
    {
        if ($request->user()->cannot('update', $role)) {
            return null;
        }

        $updateRoleAction->execute($role, $request->all());

        return $this->flashMessage('update');
    }

    public function removeRole(Role $role)
    {
        if (request()->user()->cannot('delete', $role)) {
            return null;
        }

        if ($role->members()->count() > 0) {
            throw new RoleHasMembersException;
        }

        $role->delete();

        return $this->flashMessage('delete');
    }

    /**
     * ======================
     * RENDER
     * ======================
     */
    public function render()
    {
        return Inertia::render($this->render, [
            'roles' => $this->indexRole(),
            'permissions' => $this->indexPermissions(),
            'activities' => $this->indexActivities(),
            'calendar' => $this->indexCalendar(),
            'users' => $this->indexUsers(),
            'tasks' => $this->indexTask(),
        ]);
    }
}
