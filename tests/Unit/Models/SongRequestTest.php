<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\SongRequest;
use App\Models\Onair;
use App\Models\Music;
use App\Models\User;
use App\Models\Program;
use App\Models\OAuthAccount;

class SongRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from SongRequest model relationships.
     */
    public function testOnairRelationship(): void
    {
        $user = User::factory()->create();
        $music = Music::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $onair = Onair::factory()
            ->for($program, 'program')
            ->create();


        $songRequest = SongRequest::factory()
            ->for($onair, 'onair')
            ->for($music, 'music')
            ->create();

        $this->assertInstanceOf(Onair::class, $songRequest->onair);
    }

    public function testMusicRelationship(): void
    {
        $music = Music::factory()->create();
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $onair = Onair::factory()
            ->for($program, 'program')
            ->create();

        $songRequest = SongRequest::factory()
            ->for($music, 'music')
            ->create(['onair_id' => $onair->id]);

        $this->assertTrue($songRequest->music->is($music));
    }

    public function testRequesterRelationship(): void
    {
        $oauthAccount = OAuthAccount::factory()->create();
        $user = User::factory()->create();
        $program = Program::factory()->for($user, 'host')->create();
        $onair = Onair::factory()->for($program, 'program')->create();
        $music = Music::factory()->create();

        $songRequest = SongRequest::factory()
            ->for($oauthAccount, 'requester')
            ->for($onair, 'onair')
            ->for($music, 'music')
            ->create();

        $this->assertTrue($songRequest->requester->is($oauthAccount));
    }
}
