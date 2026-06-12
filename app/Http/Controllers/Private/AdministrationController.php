<?php

namespace App\Http\Controllers\Private;

use App\Exceptions\RoleHasMembersException;
use App\Http\Controllers\Controller;
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

use App\Http\Requests\Web\Administration\CreateActivityRequest;
use App\Http\Requests\Web\Administration\CreateCalendarRequest;
use App\Http\Requests\Web\Administration\CreateRoleRequest;
use App\Http\Requests\Web\Administration\CreateTaskRequest;
use App\Http\Requests\Web\Administration\CreateUserRequest;
use App\Http\Requests\Web\Administration\UpdateActivityRequest;
use App\Http\Requests\Web\Administration\UpdateCalendarRequest;
use App\Http\Requests\Web\Administration\UpdateRoleRequest;
use App\Http\Requests\Web\Administration\UpdateTaskRequest;
use App\Http\Requests\Web\Administration\UpdateUserAccessRequest;

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
        $createActivityAction->execute($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    public function updateActivity(UpdateActivityRequest $request, Activity $activity, UpdateActivityAction $updateActivityAction)
    {
        $updateActivityAction->execute($activity, $request->user(), $request->validated());

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

    public function createCalendar(CreateCalendarRequest $request, CreateCalendarAction $createCalendarAction)
    {
        $createCalendarAction->execute(
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }

    public function updateCalendar(UpdateCalendarRequest $request, Calendar $calendar, UpdateCalendarAction $updateCalendarAction)
    {
        $updateCalendarAction->execute(
            $calendar,
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

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

    public function createTask(CreateTaskRequest $request, CreateTaskAction $createTaskAction)
    {
        $createTaskAction->execute(
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }

    public function updateTask(UpdateTaskRequest $request, Task $task, UpdateTaskAction $updateTaskAction)
    {
        $updateTaskAction->execute(
            $task,
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

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

    public function createUser(CreateUserRequest $request, CreateUserAction $createUserAction)
    {
        $createUserAction->execute($request->validated());

        return $this->flashMessage('save');
    }

    public function updateUserAccess(UpdateUserAccessRequest $request, User $user, UpdateUserAccessAction $updateUserAccessAction)
    {
        $updateUserAccessAction->execute($user, $request->validated());

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

    public function createRole(CreateRoleRequest $request, CreateRoleAction $createRoleAction)
    {
        $createRoleAction->execute($request->validated());

        return $this->flashMessage('save');
    }

    public function updateRole(UpdateRoleRequest $request, Role $role, UpdateRoleAction $updateRoleAction)
    {
        $updateRoleAction->execute($role, $request->validated());

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
