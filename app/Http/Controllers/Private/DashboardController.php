<?php

namespace App\Http\Controllers\Private;

use App\Services\ActivityService;
use App\Services\CalendarService;
use App\Services\PostService;
use App\Services\TaskService;

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
use App\Http\Controllers\Concerns\HasFlashMessages;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use HasFlashMessages;

    use ResolvesAuthorizedProps;

    private $render = 'private/Dashboard';

    public function __construct(
        private ActivityService $activityFilter,
        private CalendarService $calendarFilter,
        private PostService $postFilter,
        private TaskService $taskFilter,
    ) {}

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

    private function indexTasks()
    {
        return $this->whenCanViewAny(Task::class,
            fn () => TaskResource::collection(
                $this->taskFilter->filter([
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
        );
    }

    private function indexPosts()
    {
        return $this->whenCanViewAny(Post::class,
            fn () => PostResource::collection(
                $this->postFilter->filter([
                'user' => request()->user(),
                    'active' => true,
                    'authored_by' => request()->user(),
                    'with_count' => 'views',
                    'with' => 'author',
                    'limit' => 5,
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

    public function confirmActivityParticipant(Request $request, ActivityService $service, Activity $activity)
    {
        $this->authorize('confirmParticipation', $activity);

        $service->confirmActivityParticipant($activity, $request->user());

        return $this->flashMessage('save');
    }

    public function markTaskToReview(TaskService $service, Task $task)
    {
        $this->authorize('markForReview', $task);

        $service->markTaskToReview($task);

        return $this->flashMessage('complete');
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'activities' => $this->indexActivities(),
            'tasks' => $this->indexTasks(),
            'posts' => $this->indexPosts(),
            'calendar' => $this->indexCalendar(),
        ]);
    }
}
