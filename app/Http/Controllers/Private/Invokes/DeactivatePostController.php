<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Post\DeactivatePostAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Post;

class DeactivatePostController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private DeactivatePostAction $deactivatePostAction,
    ) {}

    public function __invoke(Post $post)
    {
        $this->authorize('deactivate', $post);

        $this->deactivatePostAction->execute($post);

        return $this->flashMessage('deactivate');
    }
}
