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
}
