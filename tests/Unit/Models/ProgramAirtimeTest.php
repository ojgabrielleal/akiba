<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Http\Resources\Program\ProgramAirtimeResource;
use App\Models\ProgramAirtime;
use App\Models\Program;
use App\Models\User;

class ProgramAirtimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from ProgramAirtime model relationships.
     */
    public function testProgramRelationship(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $schedule = ProgramAirtime::factory()
            ->for($program, 'program')
            ->create();

        $this->assertTrue($schedule->program->is($program));
    }

    public function testResourceReturnsOriginalMidnightFormat(): void
    {
        $programAirtime = ProgramAirtime::factory()->make([
            'hour' => '00:00:00',
        ]);

        $resource = ProgramAirtimeResource::make($programAirtime)->resolve();

        $this->assertSame('00:00:00', $resource['hour']);
    }

    public function testResourceReturnsOriginalNoonFormat(): void
    {
        $programAirtime = ProgramAirtime::factory()->make([
            'hour' => '12:00:00',
        ]);

        $resource = ProgramAirtimeResource::make($programAirtime)->resolve();

        $this->assertSame('12:00:00', $resource['hour']);
    }
}
