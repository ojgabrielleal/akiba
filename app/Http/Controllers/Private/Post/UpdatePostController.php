<?php

namespace App\Http\Controllers\Private\Post;

use App\Actions\Post\UpdatePostAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Post\UpdatePostRequest;
use App\Models\Post;

class UpdatePostController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdatePostRequest $request, UpdatePostAction $updatePostAction, Post $post)
    {
        $updatePostAction->execute(
            $post,
            $request->all(),
            $request->file('image'),
            $request->file('cover'),
            module: $request->input('module', 'review'),
        );

        return $this->flashMessage('update');
    }
}
