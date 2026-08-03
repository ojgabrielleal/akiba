<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\PostFilter;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\Post\PostResource;

use App\Models\Post;
use App\Services\External\AnimeNewsFeedService;

use Inertia\Inertia;

class PostPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Post';

    public function __construct(
        private PostFilter $postFilter,
        private AnimeNewsFeedService $newsFeed,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'posts' => $this->indexPosts(),
            'newsFeedSources' => $this->indexNewsFeedSources(),
            'selectedNewsFeedSource' => request()->input('source'),
            'newsFeedPosts' => $this->indexNewsFeedPosts(),
        ]);
    }

    private function indexPosts()
    {
        return $this->whenCanViewAny(Post::class,
            fn () => PostResource::collection(
                $this->postFilter->apply([
                    'user' => request()->user(),
                    'active' => true,
                    'with_count' => 'views',
                    'with' => 'author',
                    'search' => request()->input('search'),
                    'paginate' => 10,
                ])
            )->format('grid'),
        );
    }

    private function indexNewsFeedSources()
    {
        if (! request()->user()->hasPermission('post.feed.view')) {
            return null;
        }

        return $this->newsFeed->sources();
    }

    private function indexNewsFeedPosts()
    {
        if (! request()->user()->hasPermission('post.feed.view')) {
            return null;
        }

        return $this->newsFeed->paginate(
            request()->input('source'),
            6,
            (int) request()->input('feed_page', 1),
        );
    }
}
