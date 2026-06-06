<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Favority;

class FavorityTest extends TestCase
{
    use RefreshDatabase;

    public function testUsesFavoritiesTable(): void
    {
        $this->assertSame('favorities', (new Favority())->getTable());
    }

    /**
     * Tests from Favority model relationships.
     */
    public function testUserRelationship(): void
    {
        $user = User::factory()->create();

        $favorite = Favority::factory()
            ->for($user, 'user')
            ->create();

        $this->assertTrue($favorite->user->is($user));
    }
}
