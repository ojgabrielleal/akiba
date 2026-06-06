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

class ListenerMonthTest extends TestCase
{
    use RefreshDatabase;

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

        SongRequest::factory(5)
            ->for($onair, 'onair')
            ->for($music, 'music')
            ->create([
                'name' => 'John Doe',
                'address' => '123 Main St',
                'was_reproduced' => true,
            ]);

        $mostActiveListener = ListenerMonth::mostActiveListenerOfCurrentMonth();

        $this->assertNotNull($mostActiveListener);
        $this->assertEquals('John Doe', $mostActiveListener->name);
        $this->assertEquals('123 Main St', $mostActiveListener->address ?? '');
        $this->assertEquals($program->uuid, $mostActiveListener->program_uuid);
        $this->assertEquals($program->name, $mostActiveListener->program_name);
        $this->assertEquals($program->image, $mostActiveListener->program_image);
        $this->assertEquals($program->name, $mostActiveListener->favorite_program);
        $this->assertEquals(5, $mostActiveListener->requests_total);
    }
}
