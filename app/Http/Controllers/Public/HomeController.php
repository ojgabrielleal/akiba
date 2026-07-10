<?php

namespace App\Http\Controllers\Public;

use App\Actions\SongRequest\CreateSongRequestAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Onair;
use App\Models\Post;

use App\Http\Resources\OnairResource;
use App\Http\Resources\PostIndexResource;
use App\Http\Resources\PostResource;

class HomeController extends Controller
{
    private $render = 'public/Home';

    public function indexFeatured()
    {
        $posts = Post::published()->post()->featured()->take(15)->get();
        $events = Post::published()->event()->featured()->take(15)->get();
        $reviews = Post::published()->review()->with('postReviews.author')->featured()->take(15)->get();

        $feed = $posts
            ->concat($events)
            ->concat($reviews)
            ->sortByDesc('views_count')
            ->take(3)
            ->values();

        return PostIndexResource::collection($feed);
    }

    public function indexReview()
    {
        return PostResource::collection(
            Post::review()
                ->with('postReviews.author')
                ->latest()
                ->limit(5)
                ->get()
        );
    }

    public function indexPost()
    {
        return PostResource::collection(
            Post::published()
                ->latest()
                ->limit(6)
                ->get()
        )->format('summary');
    }

    public function showOnair()
    {
        return OnairResource::collection(
            Onair::live()->with('program.host')->get()
        );
    }

    public function createSongRequest(Request $request, CreateSongRequestAction $createSongRequestAction)
    {
        $data = $request->validate([
            'name' => 'required',
            'address' => 'required',
            'anime' => 'required',
            'music' => 'required',
            'message' => 'required',
        ]);

        $createSongRequestAction->execute($data, $request->ip());

        return back(303);
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'featureds' => $this->indexFeatured(),
            'reviews' => $this->indexReview(),
            'posts' => $this->indexPost(),
            'onair' => $this->showOnair(),
        ]);
    }
}
