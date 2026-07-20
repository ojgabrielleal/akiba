<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Program;
use App\Models\Onair;
use App\Models\SongRequest;
use App\Models\Music;
use App\Models\ListenerMonth;
use App\Models\OAuthAccount;

class ListenerMonthTest extends TestCase
{
    use RefreshDatabase;

    public function testOAuthAccountRelationship(): void
    {
        $oauthAccount = OAuthAccount::factory()->create();
        $listenerMonth = ListenerMonth::factory()
            ->for($oauthAccount, 'oauthAccount')
            ->create();

        $this->assertTrue($listenerMonth->oauthAccount->is($oauthAccount));
    }

    /**
     * Tests from ListenerMonth model static methods.
     */
    public function testMostActiveListenerMethod(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $onair = Onair::factory()
            ->for($program, 'program')
            ->create();

        $music = Music::factory()->create();
        $oauthAccount = OAuthAccount::factory()->create();

        SongRequest::factory(5)
            ->for($onair, 'onair')
            ->for($music, 'music')
            ->create([
                'oauth_account_id' => $oauthAccount->id,
                'was_reproduced' => true,
            ]);

        $mostActiveListener = ListenerMonth::mostActiveListenerOfCurrentMonth();

        $this->assertNotNull($mostActiveListener);
        $this->assertEquals($oauthAccount->id, $mostActiveListener->oauth_account_id);
        $this->assertEquals($program->name, $mostActiveListener->favorite_program['name']);
        $this->assertEquals($program->image, $mostActiveListener->favorite_program['image']);
        $this->assertEquals($music->name, $mostActiveListener->favorite_music['name']);
        $this->assertEquals(5, $mostActiveListener->requests_total);
    }
}
