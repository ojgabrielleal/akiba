<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Activity;
use App\Models\Calendar;
use App\Models\Post;
use App\Models\Task;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\Calendar\CalendarWeekResource;
use App\Http\Resources\Post\PostResource;
use App\Http\Resources\TaskResource;

class DashboardPageController extends Controller
{
    private $render = 'private/Dashboard';

    public function render()
    {
        return Inertia::render($this->render, [
            'activities' => $this->indexActivities(),
            'tasks' => $this->indexTasks(),
            'posts' => $this->indexPosts(),
            'calendar' => $this->indexCalendar(),
        ]);
    }

    public function indexActivities()
    {
        $this->authorize('viewAny', Activity::class);

        return ActivityResource::collection(
            Activity::valid()
                ->with(['author', 'confirmations'])
                ->latest()
                ->get()
        );
    }

    public function indexTasks()
    {
        $this->authorize('viewAny', Task::class);

        return TaskResource::collection(
            Task::active()
                ->incompleted()
                ->mine()
                ->where('status', '!=', 'completed')
                ->with(['responsible'])
                ->orderBy('dead_line')
                ->orderBy('created_at', 'desc')
                ->paginate(5)
        );
    }

    public function indexPosts()
    {
        $this->authorize('viewAny', Post::class);

        return PostResource::collection(
            Post::active()
                ->published()
                ->mine()
                ->with(['author', 'views'])
                ->limit(5)
                ->get()
        )->format('summary');
    }

    public function indexCalendar()
    {
        $this->authorize('viewAny', Calendar::class);

        return CalendarWeekResource::make(
            Calendar::valid()
                ->with(['activity', 'responsible'])
                ->get()
        );
    }
}
