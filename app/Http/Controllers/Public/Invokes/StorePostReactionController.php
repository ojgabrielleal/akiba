<?php

namespace App\Http\Controllers\Public\Invokes;

use App\Actions\Post\StorePostReactionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostReactionRequest;
use App\Models\Post;
use App\Support\AuthenticatedMember;
use Illuminate\Http\RedirectResponse;

class StorePostReactionController extends Controller
{
    public function __invoke(StorePostReactionRequest $request, StorePostReactionAction $action, Post $post): RedirectResponse
    {
        $action->execute(
            $post,
            AuthenticatedMember::fromRequest($request),
            $request->validated('name'),
        );

        return back(303);
    }
}
