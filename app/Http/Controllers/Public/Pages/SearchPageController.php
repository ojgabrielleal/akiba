<?php

namespace App\Http\Controllers\Public\Pages;

use App\Filters\CalendarFilter;
use App\Filters\PodcastFilter;
use App\Filters\PostFilter;
use App\Filters\ProgramFilter;
use App\Filters\UserFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Calendar\CalendarResource;
use App\Http\Resources\PodcastResource;
use App\Http\Resources\Post\PostResource;
use App\Http\Resources\Program\ProgramResource;
use App\Http\Resources\User\UserResource;
use Inertia\Inertia;

class SearchPageController extends Controller
{
    public function __construct(
        private PostFilter $postFilter,
        private PodcastFilter $podcastFilter,
        private ProgramFilter $programFilter,
        private CalendarFilter $calendarFilter,
        private UserFilter $userFilter,
    ) {}

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

    private function indexEditorialResults(string $search)
    {
        return PostResource::collection(
            $this->postFilter->apply([
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
            $this->podcastFilter->apply([
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
            $this->programFilter->apply([
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
            $this->calendarFilter->apply([
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
            $this->userFilter->apply([
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
                'href' => "/equipe#membro-{$member['uuid']}",
                'image' => $member['avatar'],
                'description' => $member['highest_role']['label'] ?? null,
            ])
            ->all();
    }
}
