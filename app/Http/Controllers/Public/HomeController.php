<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\External\CastService;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Event;
use App\Models\Music;
use App\Models\Onair;
use App\Models\Post;
use App\Models\Review;

use App\Http\Resources\OnairResource;
use App\Http\Resources\PostIndexResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReviewResource;

class HomeController extends Controller
{
    private $render = 'public/Home';

    protected $cast;

    public function __construct(CastService $cast)
    {
        $this->cast = $cast;
    }

    public function indexFeatured()
    {
        $posts = Post::published()->featured()->take(15)->get();
        $events = Event::featured()->take(15)->get();
        $reviews = Review::with('opinions.author')->featured()->take(15)->get();

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
        return ReviewResource::collection(
            Review::latest()
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
        $cast = $this->cast->data();
        $onair = Onair::live()->with('program.host')->get();

        $onair->each(function ($item) use ($cast) {
            $item->current_song = $cast['current_song'] ?? null;
        });

        return OnairResource::collection($onair);
    }

    public function createSongRequest(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'anime' => 'required',
            'music' => 'required',
            'message' => 'required',
        ]);

        $onair = Onair::live()->first();

        $music = Music::where('name', $request->input('music.name'))->first();
        if (! $music) {
            $music = Music::create([
                'production' => $request->input('anime.title'),
                'type' => $request->input('music.type'),
                'artist' => $request->input('music.artist'),
                'name' => $request->input('music.name'),
                'image' => $request->input('anime.image'),
            ]);
        } else {
            $music->increment('song_requests_total');
        }

        $onair->songRequests()->create([
            'ip_address' => $request->ip(),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'message' => $request->input('message'),
            'music_id' => $music->id,
        ]);

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
