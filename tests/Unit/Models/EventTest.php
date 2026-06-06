<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Event;
use App\Models\Post;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Event model relationships.
     */
    public function testPostRelationship(): void
    {
        $post = Post::factory()->create();

        $event = Event::factory()
            ->for($post, 'post')
            ->create();

        $this->assertTrue($event->post->is($post));
    }

    public function testUsesEventsTable(): void
    {
        $this->assertSame('events', (new Event())->getTable());
    }
}
