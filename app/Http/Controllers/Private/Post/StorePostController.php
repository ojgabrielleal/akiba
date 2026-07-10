<?php

namespace App\Http\Controllers\Private\Post;

use App\Actions\Post\CreatePostAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Post\CreatePostRequest;

class StorePostController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreatePostRequest $request, CreatePostAction $createPostAction)
    {
        $createPostAction->execute(
            $request->user(),
            $request->all(),
            $request->file('image'),
            $request->file('cover'),
            module: $request->input('module', 'post'),
        );

        return $this->flashMessage('save');
    }
}
