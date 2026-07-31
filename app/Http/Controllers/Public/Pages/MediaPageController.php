<?php

namespace App\Http\Controllers\Public\Pages;

use App\Filters\ListenerGalleryFilter;
use App\Filters\PollFilter;
use App\Filters\PostFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ListenerGalleryResource;
use App\Http\Resources\Poll\PollResource;
use App\Http\Resources\Post\PostResource;
use Inertia\Inertia;

class MediaPageController extends Controller
{
    public function __construct(
        private ListenerGalleryFilter $listenerGalleryFilter,
        private PollFilter $pollFilter,
        private PostFilter $postFilter,
    ) {}

    public function render()
    {
        return Inertia::render('public/Media', [
            'events' => $this->indexEvents(),
            'listenerGallery' => $this->indexListenerGallery(),
            'polls' => $this->indexPolls(),
            'latestPoll' => $this->indexLatestPoll(),
        ]);
    }

    private function indexEvents()
    {
        return PostResource::collection(
            $this->postFilter->apply([
                'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'event',
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 4,
                'ignore_authorization' => true,
            ])
        )->format('home-list');
    }

    private function indexListenerGallery()
    {
        return ListenerGalleryResource::collection(
            $this->listenerGalleryFilter->apply([
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 5,
            ])
        );
    }

    private function indexLatestPoll()
    {
        $poll = $this->pollFilter->apply([
            'active' => true,
            'open' => true,
            'with' => [
                'votes',
                'options' => fn ($query) => $query->withCount('votes'),
            ],
            'with_count' => 'votes',
            'order_by' => 'created_at',
            'order_direction' => 'desc',
            'first' => true,
        ]);

        return $poll ? PollResource::make($poll) : null;
    }

    private function indexPolls()
    {
        return PollResource::collection(
            $this->pollFilter->apply([
                'active' => true,
                'open' => true,
                'with' => [
                    'votes',
                    'options' => fn ($query) => $query->withCount('votes'),
                ],
                'with_count' => 'votes',
                'order_by' => 'created_at',
                'order_direction' => 'desc',
            ])
        );
    }
}
