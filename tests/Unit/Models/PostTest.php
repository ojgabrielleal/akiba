<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Post;
use App\Models\Reference;
use App\Models\Reaction;
use App\Models\Tag;
use App\Models\Event;
use App\Models\Review;
use App\Models\PageView;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Post model relationships.
     */
    public function testPostRelationModelsUseRenamedTables(): void
    {
        $this->assertSame('references', (new Reference())->getTable());
        $this->assertSame('reactions', (new Reaction())->getTable());
        $this->assertSame('tags', (new Tag())->getTable());
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
        $reference = Reference::factory(2);

        $post = Post::factory()
            ->for($user, 'author')
            ->has($reference, 'references')
            ->create();

        $firstReference = $post->references->first();

        $this->assertCount(2, $post->references);
        $this->assertContainsOnlyInstancesOf(Reference::class, $post->references);
        $this->assertNotNull($firstReference);
        $this->assertTrue($firstReference->post->is($post));
    }

    public function testReactionsRelationship(): void
    {
        $user = User::factory()->create();
        $reaction = Reaction::factory(2);

        $post = Post::factory()
            ->for($user, 'author')
            ->has($reaction, 'reactions')
            ->create();

        $firstReaction = $post->reactions->first();

        $this->assertCount(2, $post->reactions);
        $this->assertContainsOnlyInstancesOf(Reaction::class, $post->reactions);
        $this->assertNotNull($firstReaction);
        $this->assertTrue($firstReaction->post->is($post));
    }

    public function testTagsRelationship(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory(2);

        $post = Post::factory()
            ->for($user, 'author')
            ->has($tag, 'tags')
            ->create();

        $firstTag = $post->tags->first();

        $this->assertCount(2, $post->tags);
        $this->assertContainsOnlyInstancesOf(Tag::class, $post->tags);
        $this->assertNotNull($firstTag);
        $this->assertTrue($firstTag->post->is($post));
    }

    public function testEventRelationship(): void
    {
        $user = User::factory()->create();
        $event = Event::factory();

        $post = Post::factory()
            ->for($user, 'author')
            ->has($event, 'event')
            ->create();

        $this->assertTrue($post->event->post->is($post));
    }

    public function testReviewRelationship(): void
    {
        $user = User::factory()->create();
        $review = Review::factory();

        $post = Post::factory()
            ->for($user, 'author')
            ->has($review, 'review')
            ->create();

        $this->assertTrue($post->review->post->is($post));
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
