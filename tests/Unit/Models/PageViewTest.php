<?php

namespace Tests\Unit\Models;

use App\Models\PageView;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageViewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from PageView model relationships.
     */
    public function testViewableRelationship(): void
    {
        $post = Post::factory()->create();

        $pageView = PageView::factory()
            ->for($post, 'viewable')
            ->create();

        $this->assertTrue($pageView->viewable->is($post));
    }
}
