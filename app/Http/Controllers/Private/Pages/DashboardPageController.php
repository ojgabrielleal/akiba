<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\ActivityFilter;
use App\Filters\CalendarFilter;
use App\Filters\PostFilter;
use App\Filters\TaskFilter;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\Calendar\CalendarWeekResource;
use App\Http\Resources\Post\PostResource;
use App\Http\Resources\TaskResource;

use App\Models\Activity;
use App\Models\Calendar;
use App\Models\Post;
use App\Models\Task;

use Inertia\Inertia;

class DashboardPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Dashboard';

    public function __construct(
        private ActivityFilter $activityFilter,
        private CalendarFilter $calendarFilter,
        private PostFilter $postFilter,
        private TaskFilter $taskFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'activities' => $this->whenCanViewAny(Activity::class,
                fn () => ActivityResource::collection(
                    $this->activityFilter->apply([
                        'not_expired' => true,
                        'with' => ['author', 'confirmations'],
                    ])
                ),
            ),
            'tasks' => $this->whenCanViewAny(Task::class,
                fn () => TaskResource::collection(
                    $this->taskFilter->apply([
                        'active' => true,
                        'incomplete' => true,
                        'assigned_to' => request()->user(),
                        'with' => ['responsible'],
                        'order_by' => 'dead_line',
                        'order_direction' => 'asc',
                        'then_order_by' => 'created_at',
                        'then_order_direction' => 'desc',
                        'paginate' => 5,
                    ])
                ),
            ),
            'posts' => $this->whenCanViewAny(Post::class,
                fn () => PostResource::collection(
                    $this->postFilter->apply(request()->user(), [
                        'active' => true,
                        'status' => 'published',
                        'authored_by' => request()->user(),
                        'with_count' => 'views',
                        'with' => 'author',
                        'limit' => 5,
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
        ]);
    }
}
