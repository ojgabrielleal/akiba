<?php

namespace App\Http\Controllers\Public\Invokes;

use App\Actions\Post\StorePostCommentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostCommentRequest;
use App\Models\Post;

use Illuminate\Http\RedirectResponse;

class StorePostCommentController extends Controller
{
    public function __invoke(StorePostCommentRequest $request, StorePostCommentAction $action, Post $post): RedirectResponse
    {
        $action->execute(
            $post,
            $request->attributes->get('oauth_account'),
            $request->validated(),
        );

        return back(303);
    }
}
