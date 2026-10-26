<?php

namespace Tests\Feature\Public;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostShortLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_posts_receive_a_stable_unique_short_code(): void
    {
        $first = Post::factory()->create(['title' => 'Título original']);
        $second = Post::factory()->create();
        $shortCode = $first->short_code;

        $first->update(['title' => 'Título atualizado']);

        $this->assertMatchesRegularExpression('/^[a-z0-9]{8}$/', $shortCode);
        $this->assertNotSame($shortCode, $second->short_code);
        $this->assertSame($shortCode, $first->fresh()->short_code);
    }

    public function test_short_link_redirects_each_post_module_to_its_canonical_url(): void
    {
        foreach ([
            'post' => 'post.read',
            'review' => 'review.read',
            'event' => 'event.read',
        ] as $module => $route) {
            $post = Post::factory()->forModule($module)->create([
                'is_active' => true,
                'status' => 'published',
            ]);

            $this->get(route('post.short', $post->short_code))
                ->assertRedirect(route($route, $post->slug))
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
    }

    public function test_short_link_does_not_expose_inactive_or_unpublished_posts(): void
    {
        $inactive = Post::factory()->create([
            'is_active' => false,
            'status' => 'published',
        ]);
        $draft = Post::factory()->create([
            'is_active' => true,
            'status' => 'draft',
        ]);

        $this->get(route('post.short', $inactive->short_code))->assertNotFound();
        $this->get(route('post.short', $draft->short_code))->assertNotFound();
    }
}
