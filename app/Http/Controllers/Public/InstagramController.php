<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\InstagramStoryService;
use App\Services\PageViewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstagramController extends Controller
{
    public function redirect(
        Request $request,
        InstagramStoryService $stories,
        PageViewService $pageViews,
        Post $post,
    ): RedirectResponse {
        abort_unless($stories->isPromoted($post), 404);

        $pageViews->store($post, $request);

        return redirect()->away($stories->destination($post));
    }

    public function render(InstagramStoryService $stories): Response
    {
        return Inertia::render('public/Instagram', [
            'stories' => $stories->promotedPosts(),
        ]);
    }
}
