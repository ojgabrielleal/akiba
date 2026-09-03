<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Program;
use App\Models\Repository;
use App\Models\Task;
use App\Models\User;
use App\Services\PodcastService;
use App\Services\PollService;
use App\Services\PostService;
use App\Services\ProgramService;
use App\Services\RepositoryService;
use App\Services\TaskService;
use App\Services\TrashService;
use App\Services\UserService;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TrashController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Trash';

    private const MODELS = [
        'user' => User::class,
        'program' => Program::class,
        'post' => Post::class,
        'podcast' => Podcast::class,
        'poll' => Poll::class,
        'task' => Task::class,
        'repository' => Repository::class,
    ];

    public function __construct(
        private PodcastService $podcastFilter,
        private PollService $pollFilter,
        private PostService $postFilter,
        private ProgramService $programFilter,
        private RepositoryService $repositoryFilter,
        private TaskService $taskFilter,
        private UserService $userFilter,
        private CacheService $cache,
    ) {}

    private function trashItems(): array
    {
        return $this->cache->remember([
            'panel',
            'trash',
            request()->user()->uuid,
        ], fn () => collect()
            ->concat($this->indexUsers())
            ->concat($this->indexPrograms())
            ->concat($this->indexPosts())
            ->concat($this->indexPodcasts())
            ->concat($this->indexPolls())
            ->concat($this->indexTasks())
            ->concat($this->indexRepositories())
            ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all(), null, ['trash', 'users', 'posts', 'reviews', 'events', 'podcasts', 'polls', 'tasks', 'repositories']);
    }

    private function indexUsers(): Collection
    {
        return $this->userFilter->filter(['active' => false])
            ->map(fn (User $user) => $this->item(
                $user, 'user', 'Usuário', $user->nickname, $user->name,
                $user->avatar, $user->gender,
            ));
    }

    private function indexPrograms(): Collection
    {
        return $this->programFilter->filter(['active' => false])
            ->map(fn (Program $program) => $this->item(
                $program, 'program', 'Programa', $program->name,
                ucfirst($program->execution_mode), $program->image,
            ));
    }

    private function indexPosts(): Collection
    {
        return $this->postFilter->filter([
            'user' => request()->user(),
            'active' => false,
            'ignore_authorization' => true,
        ])->map(fn (Post $post) => $this->item(
            $post, 'post', $this->postType($post), $post->title,
            $post->status, $post->cover,
        ));
    }

    private function indexPodcasts(): Collection
    {
        return $this->podcastFilter->filter(['active' => false])
            ->map(fn (Podcast $podcast) => $this->item(
                $podcast, 'podcast', 'Podcast', $podcast->title,
                "Temporada {$podcast->season} · Episódio {$podcast->episode}", $podcast->image,
            ));
    }

    private function indexPolls(): Collection
    {
        return $this->pollFilter->filter(['active' => false])
            ->map(fn (Poll $poll) => $this->item(
                $poll, 'poll', 'Enquete', $poll->question, $poll->status,
            ));
    }

    private function indexTasks(): Collection
    {
        return $this->taskFilter->filter(['active' => false])
            ->map(fn (Task $task) => $this->item(
                $task, 'task', 'Tarefa', $task->title, $task->status,
            ));
    }

    private function indexRepositories(): Collection
    {
        return $this->repositoryFilter->filter(['active' => false])
            ->map(fn (Repository $repository) => $this->item(
                $repository, 'repository', 'Marketing', $repository->name,
                ucfirst($repository->type), $repository->image,
            ));
    }

    private function item(
        $model,
        string $type,
        string $typeLabel,
        string $title,
        ?string $subtitle = null,
        ?string $image = null,
        ?string $gender = null,
    ): array {
        return [
            'uuid' => $model->uuid,
            'type' => $type,
            'type_label' => $typeLabel,
            'title' => $title,
            'subtitle' => $subtitle,
            'image' => $image,
            'gender' => $gender,
        ];
    }

    private function postType(Post $post): string
    {
        return match ($post->module) {
            'review' => 'Review',
            'event' => 'Evento',
            default => 'Matéria',
        };
    }

    public function reactivateTrashItem(TrashService $service, string $type, string $uuid)
    {
        Gate::authorize('trash.restore');

        $item = $this->resolveItem($type, $uuid);
        $service->reactivate($item);

        return $this->flashMessage('update', 'Item reativado com sucesso.', '♻️');
    }

    public function destroyTrashItem(TrashService $service, string $type, string $uuid)
    {
        Gate::authorize('trash.delete');

        $item = $this->resolveItem($type, $uuid);
        $service->destroy($item);

        return $this->flashMessage('delete', 'Item excluído definitivamente.');
    }

    private function resolveItem(string $type, string $uuid): Model
    {
        abort_unless(array_key_exists($type, self::MODELS), 404);

        return self::MODELS[$type]::query()
            ->where('is_active', false)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'trash_items' => $this->trashItems(),
        ]);
    }
}
