<?php

namespace App\Http\Controllers\Private\Post;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Post;

class DeactivatePostController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Post $post)
    {
        $this->authorize('delete', $post);

        $post->update([
            'is_active' => false,
        ]);

        return $this->flashMessage('deactivate');
    }
}
