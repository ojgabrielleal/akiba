<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\PodcastFilter;
use App\Filters\PollFilter;
use App\Filters\PostFilter;
use App\Filters\ProgramFilter;
use App\Filters\RepositoryFilter;
use App\Filters\TaskFilter;
use App\Filters\UserFilter;
use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Program;
use App\Models\Repository;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class InactiveItemsPageController extends Controller
{
    private $render = 'private/InactiveItems';

    public function __construct(
        private PodcastFilter $podcastFilter,
        private PollFilter $pollFilter,
        private PostFilter $postFilter,
        private ProgramFilter $programFilter,
        private RepositoryFilter $repositoryFilter,
        private TaskFilter $taskFilter,
        private UserFilter $userFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'inactive_items' => $this->inactiveItems(),
        ]);
    }

    private function inactiveItems(): array
    {
        return collect()
            ->concat($this->users())
            ->concat($this->programs())
            ->concat($this->posts())
            ->concat($this->podcasts())
            ->concat($this->polls())
            ->concat($this->tasks())
            ->concat($this->repositories())
            ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function users(): Collection
    {
        return $this->userFilter->apply(['active' => false])
            ->map(fn (User $user) => $this->item(
                $user, 'user', 'Usuário', $user->nickname, $user->name,
                $user->avatar, $user->gender,
            ));
    }

    private function programs(): Collection
    {
        return $this->programFilter->apply(['active' => false])
            ->map(fn (Program $program) => $this->item(
                $program, 'program', 'Programa', $program->name,
                ucfirst($program->execution_mode), $program->image,
            ));
    }

    private function posts(): Collection
    {
        return $this->postFilter->apply(request()->user(), [
            'active' => false,
            'ignore_authorization' => true,
        ])->map(fn (Post $post) => $this->item(
            $post, 'post', $this->postType($post), $post->title,
            $post->status, $post->cover,
        ));
    }

    private function podcasts(): Collection
    {
        return $this->podcastFilter->apply(['active' => false])
            ->map(fn (Podcast $podcast) => $this->item(
                $podcast, 'podcast', 'Podcast', $podcast->title,
                "Temporada {$podcast->season} · Episódio {$podcast->episode}", $podcast->image,
            ));
    }

    private function polls(): Collection
    {
        return $this->pollFilter->apply(['active' => false])
            ->map(fn (Poll $poll) => $this->item(
                $poll, 'poll', 'Enquete', $poll->question, $poll->status,
            ));
    }

    private function tasks(): Collection
    {
        return $this->taskFilter->apply(['active' => false])
            ->map(fn (Task $task) => $this->item(
                $task, 'task', 'Tarefa', $task->title, $task->status,
            ));
    }

    private function repositories(): Collection
    {
        return $this->repositoryFilter->apply(['active' => false])
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
}
