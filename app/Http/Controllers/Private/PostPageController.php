<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Queries\Post\ListPostQuery;

class PostPageController extends Controller
{
    private $render = 'private/Post';

    public function render(ListPostQuery $listPostQuery)
    {
        return Inertia::render($this->render, [
            'posts' => $listPostQuery->handle(request()->user()),
        ]);
    }

}
