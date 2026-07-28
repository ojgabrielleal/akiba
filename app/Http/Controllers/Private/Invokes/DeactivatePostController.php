<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Post\DeactivatePostAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Post;

class DeactivatePostController extends Controller
{
    use HasFlashMessages;

    public function __invoke(DeactivatePostAction $action, Post $post)
    {
        $this->authorize('deactivate', $post);

        $action->execute($post);

        return $this->flashMessage('deactivate');
    }
}
