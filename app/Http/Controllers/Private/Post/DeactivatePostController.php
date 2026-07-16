<?php

namespace App\Http\Controllers\Private\Post;

use App\Actions\Post\DeactivatePostAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Post;

class DeactivatePostController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Post $post, DeactivatePostAction $deactivatePostAction)
    {
        $this->authorize('delete', $post);

        $deactivatePostAction->execute($post);

        return $this->flashMessage('deactivate');
    }
}
