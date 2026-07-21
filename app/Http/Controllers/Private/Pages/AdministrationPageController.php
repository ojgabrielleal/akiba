<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\ActivityFilter;
use App\Filters\CalendarFilter;
use App\Filters\PermissionFilter;
use App\Filters\RoleFilter;
use App\Filters\TaskFilter;
use App\Filters\UserFilter;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\Calendar\CalendarWeekResource;
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

class AdministrationPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Administration';

    public function __construct(
        private ActivityFilter $activityFilter,
        private CalendarFilter $calendarFilter,
        private PermissionFilter $permissionFilter,
        private RoleFilter $roleFilter,
        private TaskFilter $taskFilter,
        private UserFilter $userFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'roles' => $this->whenCanViewAny(Role::class,
                fn () => RoleResource::collection(
                    $this->roleFilter->apply([
                        'with_count' => 'members',
                        'with' => 'permissions',
                    ])
                ),
            ),
            'permissions' => $this->whenCanViewAny(Role::class,
                fn () => PermissionResource::collection(
                    $this->permissionFilter->apply()
                ),
            ),
            'activities' => $this->whenCanViewAny(Activity::class,
                fn () => ActivityResource::collection(
                    $this->activityFilter->apply([
                        'not_expired' => true,
                        'with' => ['author', 'confirmations'],
                    ])
                ),
            ),
            'calendar' => $this->whenCanViewAny(Calendar::class,
                fn () => CalendarWeekResource::make(
                    $this->calendarFilter->apply([
                        'upcoming' => true,
                        'with' => ['activity', 'responsible'],
                    ])
                ),
            ),
            'users' => $this->whenCanViewAny(User::class,
                fn () => UserResource::collection(
                    $this->userFilter->apply([
                        'active' => true,
                        'with' => ['roles'],
                    ])
                )->format('summary'),
            ),
            'tasks' => $this->whenCanViewAny(Task::class,
                fn () => TaskResource::collection(
                    $this->taskFilter->apply([
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
            ),
        ]);
    }
}
