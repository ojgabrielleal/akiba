<?php

namespace Tests\Unit\Models;

use App\Models\PageView;
use App\Models\Post;

use App\Models\PostReaction;
use App\Models\PostReference;
use App\Models\PostReview;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Post model relationships.
     */
    public function test_post_relation_models_use_renamed_tables(): void
    {
        $this->assertSame('post_references', (new PostReference)->getTable());
        $this->assertSame('post_reactions', (new PostReaction)->getTable());
        $this->assertSame('post_tags', (new PostTag)->getTable());
    }

    public function test_author_relationship(): void
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->for($user, 'author')
            ->create();

        $this->assertTrue($post->author->is($user));
    }

    public function test_references_relationship(): void
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

    public function test_reactions_relationship(): void
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

    public function test_tags_relationship(): void
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

    public function test_event_factory_state(): void
    {
        $post = Post::factory()
            ->forModule('event')
            ->create();

        $this->assertSame('event', $post->module);
        $this->assertArrayHasKey('dates', $post->metadata);
        $this->assertArrayHasKey('address', $post->metadata);
    }

    public function test_review_factory_state(): void
    {
        $post = Post::factory()
            ->forModule('review')
            ->create();

        $this->assertSame('review', $post->module);
        $this->assertNotNull($post->metadata['studio']);
        $this->assertArrayHasKey('date_of_release', $post->metadata);
        $this->assertArrayHasKey('sinopse', $post->metadata);
    }

    public function test_social_attribute_is_stored_as_json_and_cast_to_array(): void
    {
        $social = [
            'text' => 'Conteúdo novo na Rede Akiba.',
            'hashtags' => ['anime', 'redeakiba'],
            'channels' => [
                'instagram' => true,
            ],
        ];

        $post = Post::factory()->create(['social' => $social]);

        $this->assertEquals($social, $post->fresh()->social);
        $this->assertJson((string) $post->getRawOriginal('social'));
    }

    public function test_post_reviews_relationship(): void
    {
        $postReviews = PostReview::factory(2);

        $post = Post::factory()
            ->forModule('review')
            ->has($postReviews, 'postReviews')
            ->create();

        $this->assertCount(2, $post->postReviews);
        $this->assertContainsOnlyInstancesOf(PostReview::class, $post->postReviews);
    }

    public function test_views_relationship(): void
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
    public function test_active_scope(): void
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

    public function test_published_scope(): void
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

        $publishedPosts = Post::withStatus('published')->get();

        $this->assertTrue($publishedPosts->contains($publishedPost));
        $this->assertFalse($publishedPosts->contains($draftPost));
    }

    public function test_authored_by_scope(): void
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

        $myPosts = Post::authoredBy($user)->get();

        $this->assertTrue($myPosts->contains($myPost));
        $this->assertFalse($myPosts->contains($otherPost));
    }

    public function test_featured_scope(): void
    {
        $featuredPost = Post::factory()->create();
        $regularPost = Post::factory()->create();

        PageView::factory(3)
            ->for($featuredPost, 'viewable')
            ->create();

        PageView::factory()
            ->for($regularPost, 'viewable')
            ->create();

        $posts = Post::withCount('views')->orderByMostViewed()->get()->keyBy('id');

        $this->assertSame(3, $posts[$featuredPost->id]->views_count);
        $this->assertSame(1, $posts[$regularPost->id]->views_count);
    }

    /**
     * Tests from Post model attributes.
     */
    public function test_slug_attribute(): void
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->for($user, 'author')
            ->create([
                'title' => 'Sample Post Title',
            ]);

        $this->assertEquals('sample-post-title', $post->slug);
    }
}
