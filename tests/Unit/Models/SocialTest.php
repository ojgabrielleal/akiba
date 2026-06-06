<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Social;

class SocialTest extends TestCase
{
    use RefreshDatabase;

    public function testUsesSocialsTable(): void
    {
        $this->assertSame('socials', (new Social())->getTable());
    }

    /**
     * Tests from Social model relationships.
     */
    public function testUserRelationship(): void
    {
        $user = User::factory()->create();

        $social = Social::factory()
            ->for($user, 'user')
            ->create();

        $this->assertTrue($social->user->is($user));
    }
}
