<?php

namespace Tests\Unit\Services;

use App\Models\Post;
use App\Models\User;
use App\Services\PageViewService;
use Illuminate\Http\Request;
use Tests\TestCase;

class PageViewServiceTest extends TestCase
{
    public function test_post_author_view_is_not_recorded(): void
    {
        $author = (new User)->forceFill(['id' => 10]);
        $post = (new Post)->forceFill([
            'id' => 20,
            'user_id' => $author->id,
        ]);
        $request = Request::create('/materia/teste');
        $request->setUserResolver(fn (): User => $author);

        $this->assertNull(app(PageViewService::class)->store($post, $request));
    }
}
