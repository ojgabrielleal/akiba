<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Preference;

class PreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function testUsesPreferencesTable(): void
    {
        $this->assertSame('preferences', (new Preference())->getTable());
    }

    /**
     * Tests from Preference model relationships.
     */
    public function testUserRelationship(): void
    {
        $user = User::factory()->create();

        $preference = Preference::factory()
            ->for($user, 'user')
            ->create();

        $this->assertTrue($preference->user->is($user));
    }
}
