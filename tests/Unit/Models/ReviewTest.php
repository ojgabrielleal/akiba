<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Opinion;
use App\Models\Review;
use App\Models\Post;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Review model relationships.
     */
    public function testPostRelationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $review = Review::factory()
            ->for($post, 'post')
            ->create();

        $this->assertTrue($review->post->is($post));
    }

    public function testOpinionsRelationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $opinions = Opinion::factory()
            ->for($user, 'author');

        $review = Review::factory()
            ->for($post, 'post')
            ->has($opinions, 'opinions')
            ->create();

        $this->assertContainsOnlyInstancesOf(Opinion::class, $review->opinions);
    }

    public function testUsesReviewsTable(): void
    {
        $this->assertSame('reviews', (new Review())->getTable());
    }
}
