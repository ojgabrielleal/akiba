<?php

namespace App\Http\Controllers\Private;

use App\Services\ActivityService;
use App\Services\CalendarService;
use App\Services\FormSubmissionService;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Services\TaskService;
use App\Services\UserService;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\Calendar\CalendarWeekResource;
use App\Http\Resources\FormSubmissionResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\User\UserResource;

use App\Models\Activity;
use App\Models\Calendar;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;

use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Services\RepositoryService;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Http\Requests\Calendar\StoreCalendarRequest;
use App\Http\Requests\Calendar\UpdateCalendarRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserAccessRequest;
use App\Http\Resources\Calendar\CalendarResource;
use App\Http\Resources\RepositoryResource;
use App\Models\FormSubmission;
use App\Models\Repository;
use App\Exceptions\RoleHasMembersException;

class AdministrationController extends Controller
{
    use HasFlashMessages;
    use ResolvesAuthorizedProps;

    private $render = 'private/Administration';

    public function __construct(
        private ActivityService $activityFilter,
        private CalendarService $calendarFilter,
        private FormSubmissionService $formSubmissionFilter,
        private PermissionService $permissionFilter,
        private RoleService $roleFilter,
        private RepositoryService $repositoryFilter,
        private TaskService $taskFilter,
        private UserService $userFilter,
    ) {}

    private function indexRoles()
    {
        return $this->whenCanViewAny(Role::class,
            fn () => RoleResource::collection(
                $this->roleFilter->filter([
                    'with_count' => 'members',
                    'with' => 'permissions',
                ])
            ),
        );
    }

    private function indexPermissions()
    {
        return $this->whenCanViewAny(Role::class,
            fn () => PermissionResource::collection(
                $this->permissionFilter->filter()
            ),
        );
    }

    private function indexActivities()
    {
        return $this->whenCanViewAny(Activity::class,
            fn () => ActivityResource::collection(
                $this->activityFilter->filter([
                    'not_expired' => true,
                    'with' => ['author', 'confirmations'],
                ])
            ),
        );
    }

    private function indexCalendar()
    {
        return $this->whenCanViewAny(Calendar::class,
            fn () => CalendarWeekResource::make(
                $this->calendarFilter->filter([
                    'upcoming' => true,
                    'with' => ['activity', 'responsible'],
                ])
            ),
        );
    }

    private function indexUsers()
    {
        return $this->whenCanViewAny(User::class,
            fn () => UserResource::collection(
                $this->userFilter->filter([
                    'active' => true,
                    'virtual_last' => true,
                    'with' => ['roles'],
                ])
            )->format('summary'),
        );
    }

    private function indexTasks()
    {
        return $this->whenCanViewAny(Task::class,
            fn () => TaskResource::collection(
                $this->taskFilter->filter([
                    'active' => true,
                    'incomplete' => true,
                    'with' => ['responsible'],
                    'order_by' => 'dead_line',
                    'order_direction' => 'asc',
                    'then_order_by' => 'created_at',
                    'then_order_direction' => 'desc',
                    'paginate' => 5,
                ])
            ),
        );
    }

    private function indexFormSubmissions()
    {
        if (! Gate::allows('form.submission.list')) {
            return null;
        }

        return FormSubmissionResource::collection(
            $this->formSubmissionFilter->filter([
                'with' => ['reviewer'],
                'status_order' => true,
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'paginate' => 10,
            ])
        );
    }

    public function showUser(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::render($this->render, [
            'user' => $this->indexUser($user),
        ]);
    }

    public function storeUser(StoreUserRequest $request, UserService $service)
    {
        $service->store($request->validated());

        return $this->flashMessage('save');
    }

    public function updateUserAccess(UpdateUserAccessRequest $request, UserService $service, User $user)
    {
        $service->updateUserAccess($user, $request->validated());

        return $this->flashMessage('save');
    }

    public function deactivateUser(UserService $service, User $user)
    {
        $this->authorize('deactivate', $user);

        $service->deactivate($user);

        return $this->flashMessage('deactivate');
    }

    public function showRole(Role $role)
    {
        $this->authorize('view', $role);

        return Inertia::render($this->render, [
            'role' => $this->indexRole($role),
        ]);
    }

    public function storeRole(StoreRoleRequest $request, RoleService $service)
    {
        $service->store($request->validated());

        return $this->flashMessage('save');
    }

    public function updateRole(UpdateRoleRequest $request, RoleService $service, Role $role)
    {
        $service->update($role, $request->validated());

        return $this->flashMessage('update');
    }

    public function destroyRole(RoleService $service, Role $role)
    {
        $this->authorize('delete', $role);

        try {
            $service->destroy($role);
        } catch (RoleHasMembersException $exception) {
            return $this->flashMessage('error', $exception->getMessage(), '⛓️');
        }

        return $this->flashMessage('delete');
    }

    public function showCalendar(Calendar $calendar)
    {
        $this->authorize('view', $calendar);

        return Inertia::render($this->render, [
            'calendarItem' => $this->indexCalendarItem($calendar),
        ]);
    }

    public function storeCalendar(StoreCalendarRequest $request, CalendarService $service)
    {
        $service->store(User::where('uuid', $request->input('user'))->firstOrFail(), $request->validated());

        return $this->flashMessage('save');
    }

    public function updateCalendar(UpdateCalendarRequest $request, CalendarService $service, Calendar $calendar)
    {
        $service->update($calendar, User::where('uuid', $request->input('user'))->firstOrFail(), $request->validated());

        return $this->flashMessage('update');
    }

    public function showActivity(Activity $activity)
    {
        $this->authorize('view', $activity);

        return Inertia::render($this->render, [
            'activity' => $this->indexActivity($activity),
        ]);
    }

    public function storeActivity(StoreActivityRequest $request, ActivityService $service)
    {
        $service->store($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    public function updateActivity(UpdateActivityRequest $request, ActivityService $service, Activity $activity)
    {
        $service->update($activity, $request->user(), $request->validated());

        return $this->flashMessage('update');
    }

    public function showTask(Task $task)
    {
        $this->authorize('view', $task);

        return Inertia::render($this->render, [
            'task' => $this->indexTask($task),
        ]);
    }

    public function storeTask(StoreTaskRequest $request, TaskService $service)
    {
        $service->store(User::where('uuid', $request->input('user'))->firstOrFail(), $request->validated());

        return $this->flashMessage('save');
    }

    public function updateTask(UpdateTaskRequest $request, TaskService $service, Task $task)
    {
        $service->update($task, User::where('uuid', $request->input('user'))->firstOrFail(), $request->validated());

        return $this->flashMessage('update');
    }

    public function completeTask(TaskService $service, Task $task)
    {
        $this->authorize('complete', $task);

        $service->complete($task);

        return $this->flashMessage('complete');
    }

    public function deactivateTask(TaskService $service, Task $task)
    {
        $this->authorize('deactivate', $task);

        $service->deactivate($task);

        return $this->flashMessage('deactivate');
    }

    public function approveFormSubmission(FormSubmission $formSubmission, FormSubmissionService $service)
    {
        $service->review($formSubmission, auth()->user(), 'approved');

        return $this->flashMessage('update');
    }

    public function rejectFormSubmission(FormSubmission $formSubmission, FormSubmissionService $service)
    {
        $service->review($formSubmission, auth()->user(), 'rejected');

        return $this->flashMessage('update');
    }

    public function showRepository(Repository $repository)
    {
        $this->authorize('view', $repository);

        return Inertia::render($this->render, [
            'repository' => $this->indexRepository($repository),
        ]);
    }

    private function indexUser(User $user): UserResource
    {
        return new UserResource($user->load([
            'roles' => fn ($query) => $query
                ->withCount('members')
                ->with('permissions'),
        ]));
    }

    private function indexRole(Role $role): RoleResource
    {
        return new RoleResource($role->loadCount('members')->load('permissions'));
    }

    private function indexCalendarItem(Calendar $calendar): CalendarResource
    {
        return new CalendarResource($calendar->load(['activity', 'responsible']));
    }

    private function indexActivity(Activity $activity): ActivityResource
    {
        return new ActivityResource($activity->load(['author', 'confirmations', 'calendar']));
    }

    private function indexTask(Task $task): TaskResource
    {
        return new TaskResource($task->load('responsible'));
    }

    private function indexRepository(Repository $repository): RepositoryResource
    {
        return new RepositoryResource($repository);
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'roles' => $this->indexRoles(),
            'permissions' => $this->indexPermissions(),
            'activities' => $this->indexActivities(),
            'calendar' => $this->indexCalendar(),
            'users' => $this->indexUsers(),
            'tasks' => $this->indexTasks(),
            'formSubmissions' => $this->indexFormSubmissions(),
        ]);
    }
}
