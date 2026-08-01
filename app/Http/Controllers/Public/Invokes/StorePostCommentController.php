<?php

namespace App\Http\Controllers\Public\Invokes;

use App\Actions\Post\StorePostCommentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostCommentRequest;
use App\Models\Post;
use App\Support\AuthenticatedMember;

use Illuminate\Http\RedirectResponse;

class StorePostCommentController extends Controller
{
    public function __invoke(StorePostCommentRequest $request, StorePostCommentAction $action, Post $post): RedirectResponse
    {
        $action->execute(
            $post,
            AuthenticatedMember::fromRequest($request),
            $request->validated(),
        );

        return back(303);
    }
}
