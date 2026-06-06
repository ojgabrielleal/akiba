<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Opinion;
use App\Models\User;
use App\Models\Review;
use App\Models\Post;

class OpinionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Opinion model relationships.
     */
    public function testAuthorRelationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();
        $review = Review::factory()
            ->for($post, 'post')
            ->create();

        $opinion = Opinion::factory()
            ->for($user, 'author')
            ->for($review, 'review')
            ->create();

        $this->assertTrue($opinion->author->is($user));
    }

    public function testReviewRelationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()
            ->for($user, 'author')
            ->create();
        $review = Review::factory()
            ->for($post, 'post')
            ->create();

        $opinion = Opinion::factory()
            ->for($user, 'author')
            ->for($review, 'review')
            ->create();

        $this->assertTrue($opinion->review->is($review));
    }

    public function testUsesOpinionsTable(): void
    {
        $this->assertSame('opinions', (new Opinion())->getTable());
    }

    public function testFactoryUsesStatus(): void
    {
        $opinion = Opinion::factory()->make([
            'status' => 'published',
        ]);

        $this->assertSame('published', $opinion->status);
    }
}
