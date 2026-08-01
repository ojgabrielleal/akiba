<?php

namespace App\Http\Controllers\Public\Invokes;

use App\Actions\Post\TogglePostLikeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\TogglePostLikeRequest;
use App\Models\Post;
use App\Support\AuthenticatedMember;
use Illuminate\Http\RedirectResponse;

class TogglePostLikeController extends Controller
{
    public function __invoke(TogglePostLikeRequest $request, TogglePostLikeAction $action, Post $post): RedirectResponse
    {
        $action->execute(
            $post,
            AuthenticatedMember::fromRequest($request),
            hash('sha256', $request->session()->getId()),
        );

        return back(303);
    }
}
