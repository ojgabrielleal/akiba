<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\UserSocial;

class UserSocialTest extends TestCase
{
    use RefreshDatabase;

    public function testUsesUserSocialsTable(): void
    {
        $this->assertSame('user_socials', (new UserSocial())->getTable());
    }

    /**
     * Tests from UserSocial model relationships.
     */
    public function testUserRelationship(): void
    {
        $user = User::factory()->create();

        $social = UserSocial::factory()
            ->for($user, 'user')
            ->create();

        $this->assertTrue($social->user->is($user));
    }
}
