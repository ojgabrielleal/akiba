<?php

namespace App\Http\Controllers\Public;

use App\Services\CalendarService;
use App\Services\PodcastService;
use App\Services\PostService;
use App\Services\ProgramService;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Calendar\CalendarResource;
use App\Http\Resources\PodcastResource;
use App\Http\Resources\Post\PostResource;
use App\Http\Resources\Program\ProgramResource;
use App\Http\Resources\User\UserResource;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function __construct(
        private PostService $postFilter,
        private PodcastService $podcastFilter,
        private ProgramService $programFilter,
        private CalendarService $calendarFilter,
        private UserService $userFilter,
    ) {}

    private function indexEditorialResults(string $search)
    {
        return PostResource::collection(
            $this->postFilter->filter([
                'user' => request()->user(),
                'with' => 'tags',
                'with_count' => 'likes',
                'search' => $search,
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'paginate' => 12,
                'ignore_authorization' => true,
            ])
        )->format('home-list');
    }

    private function indexPodcastResults(string $search): array
    {
        return collect(PodcastResource::collection(
            $this->podcastFilter->filter([
                'active' => true,
                'with' => 'author',
                'search' => $search,
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 4,
            ])
        )->resolve(request()))
            ->map(fn (array $podcast) => [
                'uuid' => $podcast['uuid'],
                'type' => 'Podcast',
                'title' => $podcast['title'],
                'href' => '/midias',
                'image' => $podcast['image'],
                'description' => $podcast['summary'],
            ])
            ->all();
    }

    private function indexProgramResults(string $search): array
    {
        return collect(ProgramResource::collection(
            $this->programFilter->filter([
                'active' => true,
                'with' => ['host', 'programAirtimes', 'schedules'],
                'search' => $search,
                'order_by' => 'name',
                'order_direction' => 'asc',
                'limit' => 4,
            ])
        )->resolve(request()))
            ->map(fn (array $program) => [
                'uuid' => $program['uuid'],
                'type' => 'Programa',
                'title' => $program['name'],
                'href' => '/radio',
                'image' => $program['image'],
                'description' => $program['host']['nickname'] ?? $program['host']['name'] ?? null,
            ])
            ->all();
    }

    private function indexCalendarResults(string $search): array
    {
        return collect(CalendarResource::collection(
            $this->calendarFilter->filter([
                'with' => ['responsible', 'activity'],
                'search' => $search,
                'order_by' => 'date',
                'order_direction' => 'asc',
                'limit' => 4,
            ])
        )->resolve(request()))
            ->map(fn (array $calendar) => [
                'uuid' => $calendar['uuid'],
                'type' => 'Agenda',
                'title' => $calendar['title'] ?? $calendar['content'],
                'href' => '/radio',
                'image' => null,
                'description' => trim("{$calendar['date']} {$calendar['hour']}"),
            ])
            ->all();
    }

    private function indexTeamResults(string $search): array
    {
        return collect(UserResource::collection(
            $this->userFilter->filter([
                'active' => true,
                'is_virtual' => false,
                'has_roles' => true,
                'with' => 'roles',
                'search' => $search,
                'order_by' => 'name',
                'order_direction' => 'asc',
                'limit' => 4,
            ])
        )
            ->format('summary')
            ->resolve(request()))
            ->map(fn (array $member) => [
                'uuid' => $member['uuid'],
                'type' => 'Equipe',
                'title' => $member['nickname'] ?? $member['name'],
                'href' => "/equipe?member={$member['slug']}",
                'image' => $member['avatar'],
                'description' => $member['highest_role']['label'] ?? null,
            ])
            ->all();
    }

    public function render()
    {
        $search = trim((string) request('q', ''));

        return Inertia::render('public/Search', [
            'search' => $search,
            'results' => $search === '' ? [] : [
                'editorial' => $this->indexEditorialResults($search),
                'podcasts' => $this->indexPodcastResults($search),
                'programs' => $this->indexProgramResults($search),
                'calendar' => $this->indexCalendarResults($search),
                'team' => $this->indexTeamResults($search),
            ],
        ]);
    }
}
