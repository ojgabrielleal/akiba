<?php

namespace App\Http\Controllers\Private\Post;

use App\Actions\Post\StorePostAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;

class StorePostController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StorePostRequest $request, StorePostAction $storePostAction)
    {
        $storePostAction->execute(
            $request->user(),
            $request->all(),
            $request->file('image'),
            $request->file('cover')
        );

        return $this->flashMessage('save');
    }
}
