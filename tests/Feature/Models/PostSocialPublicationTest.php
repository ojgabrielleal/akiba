<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\PostSocialPublication;
use App\Models\PostSocialPublicationResult;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSocialPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_preserves_the_publication_proposal_and_channel_audit_data(): void
    {
        $post = Post::factory()->create();
        $publication = PostSocialPublication::factory()->for($post)->create([
            'caption' => 'Nova matéria na Rede Akiba',
            'hashtags' => ['Akiba', 'Anime'],
            'artwork_path' => 'social/publications/artwork.jpg',
            'requested_at' => '2026-10-21 10:00:00',
        ]);
        $result = PostSocialPublicationResult::factory()->published()->for($publication, 'publication')->create([
            'channel' => 'instagram',
            'placement' => 'feed',
            'artwork_path' => 'social/publications/feed.jpg',
            'external_id' => 'instagram-media-id',
            'attempts' => 2,
            'error' => null,
            'published_at' => '2026-10-21 10:05:00',
        ]);

        $this->assertTrue($publication->post->is($post));
        $this->assertTrue($post->socialPublications->first()->is($publication));
        $this->assertSame(['Akiba', 'Anime'], $publication->hashtags);
        $this->assertSame('2026-10-21 10:00:00', $publication->requested_at->format('Y-m-d H:i:s'));
        $this->assertTrue($publication->results->first()->is($result));
        $this->assertTrue($result->publication->is($publication));
        $this->assertSame(PostSocialPublicationResult::STATUS_PUBLISHED, $result->status);
        $this->assertSame(2, $result->attempts);
        $this->assertSame('feed', $result->placement);
        $this->assertSame('social/publications/feed.jpg', $result->artwork_path);
        $this->assertSame('2026-10-21 10:05:00', $result->published_at->format('Y-m-d H:i:s'));
    }

    public function test_it_prevents_a_duplicate_channel_for_the_same_publication_version(): void
    {
        $publication = PostSocialPublication::factory()->create();
        PostSocialPublicationResult::factory()->for($publication, 'publication')->create([
            'channel' => 'instagram',
        ]);

        $this->expectException(QueryException::class);

        PostSocialPublicationResult::factory()->for($publication, 'publication')->create([
            'channel' => 'instagram',
        ]);
    }

    public function test_the_same_channel_can_be_used_by_different_publication_versions(): void
    {
        $post = Post::factory()->create();
        $firstVersion = PostSocialPublication::factory()->for($post)->create();
        $secondVersion = PostSocialPublication::factory()->for($post)->create();

        PostSocialPublicationResult::factory()->for($firstVersion, 'publication')->create([
            'channel' => 'instagram',
        ]);
        PostSocialPublicationResult::factory()->for($secondVersion, 'publication')->create([
            'channel' => 'instagram',
        ]);

        $this->assertDatabaseCount('post_social_publication_results', 2);
        $this->assertNotSame($firstVersion->uuid, $secondVersion->uuid);
    }

    public function test_feed_and_story_are_audited_separately_for_the_same_version(): void
    {
        $publication = PostSocialPublication::factory()->create();

        PostSocialPublicationResult::factory()->for($publication, 'publication')->create([
            'channel' => 'instagram',
            'placement' => 'feed',
        ]);
        PostSocialPublicationResult::factory()->for($publication, 'publication')->create([
            'channel' => 'instagram',
            'placement' => 'story',
        ]);

        $this->assertSame(
            ['feed', 'story'],
            $publication->results()->orderBy('placement')->pluck('placement')->all(),
        );
    }

    public function test_deleting_a_post_removes_its_publications_and_results(): void
    {
        $post = Post::factory()->create();
        $publication = PostSocialPublication::factory()->for($post)->create();
        PostSocialPublicationResult::factory()->for($publication, 'publication')->create();

        $post->delete();

        $this->assertDatabaseCount('post_social_publications', 0);
        $this->assertDatabaseCount('post_social_publication_results', 0);
    }
}
