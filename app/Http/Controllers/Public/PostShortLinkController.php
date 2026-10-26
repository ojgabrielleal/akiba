<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class PostShortLinkController extends Controller
{
    public function __invoke(Post $post): RedirectResponse
    {
        abort_unless($post->is_active && $post->status === 'published', 404);

        $route = match ($post->module) {
            'review' => 'review.read',
            'event' => 'event.read',
            default => 'post.read',
        };

        return redirect()
            ->route($route, $post->slug)
            ->withHeaders(['X-Robots-Tag' => 'noindex, nofollow']);
    }
}
