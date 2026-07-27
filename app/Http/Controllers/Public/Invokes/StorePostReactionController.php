<?php

namespace App\Http\Controllers\Public\Invokes;

use App\Actions\Post\StorePostReactionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostReactionRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class StorePostReactionController extends Controller
{
    public function __construct(
        private StorePostReactionAction $storePostReactionAction,
    ) {}

    public function __invoke(StorePostReactionRequest $request, Post $post): RedirectResponse
    {
        $this->storePostReactionAction->execute(
            $post,
            $request->validated('name'),
        );

        return back(303);
    }
}
