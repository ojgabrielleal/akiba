<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Post;
use App\Models\PostReference;
use App\Models\PostReaction;
use App\Models\PostReview;
use App\Models\PostTag;
use App\Models\PageView;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Post model relationships.
     */
    public function testPostRelationModelsUseRenamedTables(): void
    {
        $this->assertSame('post_references', (new PostReference())->getTable());
        $this->assertSame('post_reactions', (new PostReaction())->getTable());
        $this->assertSame('post_tags', (new PostTag())->getTable());
    }

    public function testAuthorRelationship(): void
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $this->assertTrue($post->author->is($user));
    }

    public function testReferencesRelationship(): void
    {
        $user = User::factory()->create();
        $reference = PostReference::factory(2);

        $post = Post::factory()
            ->for($user, 'author')
            ->has($reference, 'references')
            ->create();

        $firstReference = $post->references->first();

        $this->assertCount(2, $post->references);
        $this->assertContainsOnlyInstancesOf(PostReference::class, $post->references);
        $this->assertNotNull($firstReference);
        $this->assertTrue($firstReference->post->is($post));
    }

    public function testReactionsRelationship(): void
    {
        $user = User::factory()->create();
        $reaction = PostReaction::factory(2);

        $post = Post::factory()
            ->for($user, 'author')
            ->has($reaction, 'reactions')
            ->create();

        $firstReaction = $post->reactions->first();

        $this->assertCount(2, $post->reactions);
        $this->assertContainsOnlyInstancesOf(PostReaction::class, $post->reactions);
        $this->assertNotNull($firstReaction);
        $this->assertTrue($firstReaction->post->is($post));
    }

    public function testTagsRelationship(): void
    {
        $user = User::factory()->create();
        $tag = PostTag::factory(2);

        $post = Post::factory()
            ->for($user, 'author')
            ->has($tag, 'tags')
            ->create();

        $firstTag = $post->tags->first();

        $this->assertCount(2, $post->tags);
        $this->assertContainsOnlyInstancesOf(PostTag::class, $post->tags);
        $this->assertNotNull($firstTag);
        $this->assertTrue($firstTag->post->is($post));
    }

    public function testEventFactoryState(): void
    {
        $post = Post::factory()
            ->event()
            ->create();

        $this->assertSame('event', $post->module);
        $this->assertArrayHasKey('dates', $post->metadata);
        $this->assertArrayHasKey('address', $post->metadata);
    }

    public function testReviewFactoryState(): void
    {
        $post = Post::factory()
            ->review()
            ->create();

        $this->assertSame('review', $post->module);
        $this->assertArrayHasKey('year_of_release', $post->metadata);
        $this->assertArrayHasKey('sinopse', $post->metadata);
    }

    public function testPostReviewsRelationship(): void
    {
        $postReviews = PostReview::factory(2);

        $post = Post::factory()
            ->review()
            ->has($postReviews, 'postReviews')
            ->create();

        $this->assertCount(2, $post->postReviews);
        $this->assertContainsOnlyInstancesOf(PostReview::class, $post->postReviews);
    }

    public function testViewsRelationship(): void
    {
        $post = Post::factory()->create();

        PageView::factory(3)
            ->for($post, 'viewable')
            ->create();

        $firstView = $post->views->first();

        $this->assertCount(3, $post->views);
        $this->assertContainsOnlyInstancesOf(PageView::class, $post->views);
        $this->assertNotNull($firstView);
        $this->assertTrue($firstView->viewable->is($post));
    }

    /**
     * Tests from Post model scopes.
     */
    public function testActiveScope(): void
    {
        $user = User::factory()->create();

        $activePost = Post::factory()
            ->for($user, 'author')
            ->state(['is_active' => true])
            ->create();

        $inactivePost = Post::factory()
            ->for($user, 'author')
            ->state(['is_active' => false])
            ->create();

        $activePosts = Post::active()->get();

        $this->assertTrue($activePosts->contains($activePost));
        $this->assertFalse($activePosts->contains($inactivePost));
    }

    public function testPublishedScope(): void
    {
        $user = User::factory()->create();

        $publishedPost = Post::factory()
            ->for($user, 'author')
            ->state(['status' => 'published'])
            ->create();

        $draftPost = Post::factory()
            ->for($user, 'author')
            ->state(['status' => 'draft'])
            ->create();

        $publishedPosts = Post::published()->get();

        $this->assertTrue($publishedPosts->contains($publishedPost));
        $this->assertFalse($publishedPosts->contains($draftPost));
    }

    public function testMineScope(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myPost = Post::factory()
            ->for($user, 'author')
            ->create();

        $otherPost = Post::factory()
            ->for($otherUser, 'author')
            ->create();

        $this->actingAs($user);

        $myPosts = Post::mine()->get();

        $this->assertTrue($myPosts->contains($myPost));
        $this->assertFalse($myPosts->contains($otherPost));
    }

    public function testFeaturedScope(): void
    {
        $featuredPost = Post::factory()->create();
        $regularPost = Post::factory()->create();

        PageView::factory(3)
            ->for($featuredPost, 'viewable')
            ->create();

        PageView::factory()
            ->for($regularPost, 'viewable')
            ->create();

        $posts = Post::featured()->get()->keyBy('id');

        $this->assertSame(3, $posts[$featuredPost->id]->views_count);
        $this->assertSame(1, $posts[$regularPost->id]->views_count);
    }


    /**
     * Tests from Post model attributes.
     */
    public function testSlugAttribute(): void
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->for($user, 'author')
            ->create([
                'title' => 'Sample Post Title'
            ]);

        $this->assertEquals('sample-post-title', $post->slug);
    }
}
