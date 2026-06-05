<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Models\Activity;
use App\Models\Calendar;
use App\Models\Post;
use App\Models\Task;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\CalendarWeekResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TaskResource;

class DashboardController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Dashboard';

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
            Activity::valid()
                ->with(['author', 'confirmations'])
                ->latest()
                ->get()
        );
    }

    public function confirmActivityParticipant(Activity $activity)
    {
        if (request()->user()->cannot('update', $activity)) {
            return null;
        }

        $activity->confirmations()->attach(request()->user()->id);

        return $this->flashMessage('participate');
    }

    /*
     * ======================
     * TASKS
     * ======================
     */

    public function indexTasks()
    {
        if (request()->user()->cannot('viewAny', Task::class)) {
            return null;
        }

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

    public function markTaskToReview(Task $task)
    {
        if (request()->user()->cannot('update', $task)) {
            return null;
        }

        $task->update([
            'status' => 'in_review',
        ]);

        return $this->flashMessage('complete');
    }

    /*
     * ======================
     * POSTS
     * ======================
     */

    public function indexPosts()
    {
        if (request()->user()->cannot('list', Post::class)) {
            return null;
        }

        return PostResource::collection(
            Post::active()
                ->published()
                ->mine()
                ->with(['author'])
                ->limit(5)
                ->get()
        )->format('summary');
    }

    public function deactivatePost(Post $post)
    {
        if (request()->user()->cannot('delete', $post)) {
            return null;
        }

        $post->update([
            'active' => false,
        ]);

        return $this->flashMessage('deactivate');
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

        return CalendarWeekResource::make(
            Calendar::valid()
                ->with(['activity', 'responsible'])
                ->get()
        );
    }

    /*
     * ======================
     * RENDER
     * ======================
     */

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
