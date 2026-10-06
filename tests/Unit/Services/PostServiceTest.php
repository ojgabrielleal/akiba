<?php

namespace Tests\Unit\Services;

use App\Models\OAuthAccount;
use App\Models\Post;
use App\Models\PostReaction;
use App\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_reaction_toggles_same_reaction_and_updates_different_reaction(): void
    {
        $post = Post::factory()->create();
        $reactor = OAuthAccount::factory()->create();
        $service = app(PostService::class);

        $created = $service->storeReaction($post, $reactor, 'happy');

        $this->assertInstanceOf(PostReaction::class, $created);
        $this->assertDatabaseHas('post_reactions', [
            'post_id' => $post->id,
            'reactor_type' => $reactor->getMorphClass(),
            'reactor_id' => $reactor->id,
            'name' => 'happy',
        ]);

        $updated = $service->storeReaction($post, $reactor, 'angry');

        $this->assertInstanceOf(PostReaction::class, $updated);
        $this->assertSame($created->id, $updated->id);
        $this->assertDatabaseCount('post_reactions', 1);
        $this->assertDatabaseHas('post_reactions', [
            'post_id' => $post->id,
            'reactor_type' => $reactor->getMorphClass(),
            'reactor_id' => $reactor->id,
            'name' => 'angry',
        ]);

        $removed = $service->storeReaction($post, $reactor, 'angry');

        $this->assertNull($removed);
        $this->assertDatabaseMissing('post_reactions', [
            'post_id' => $post->id,
            'reactor_type' => $reactor->getMorphClass(),
            'reactor_id' => $reactor->id,
            'name' => 'angry',
        ]);
    }

    public function test_update_syncs_tags_without_creating_duplicates(): void
    {
        $post = Post::factory()->create([
            'status' => 'draft',
            'title' => 'Original title',
        ]);
        $post->tags()->createMany([
            ['name' => 'news'],
            ['name' => 'news'],
            ['name' => 'anime'],
        ]);

        $service = app(PostService::class);

        $service->update($post, $post->author, [
            'module' => 'post',
            'status' => 'draft',
            'title' => 'Updated title',
            'content' => 'Updated content',
            'tags' => [
                ['uuid' => null, 'name' => 'news'],
                ['uuid' => null, 'name' => 'manga'],
            ],
        ]);

        $this->assertSame(
            ['manga', 'news'],
            $post->refresh()->tags()->orderBy('name')->pluck('name')->all()
        );
        $this->assertDatabaseCount('post_tags', 2);

        $service->update($post, $post->author, [
            'module' => 'post',
            'status' => 'draft',
            'title' => 'Updated title again',
            'content' => 'Updated content again',
            'tags' => [
                ['uuid' => null, 'name' => 'news'],
                ['uuid' => null, 'name' => 'manga'],
            ],
        ]);

        $this->assertSame(
            ['manga', 'news'],
            $post->refresh()->tags()->orderBy('name')->pluck('name')->all()
        );
        $this->assertDatabaseCount('post_tags', 2);
    }
}
