<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Http\Resources\AirtimeResource;
use App\Models\Airtime;
use App\Models\Program;
use App\Models\User;

class AirtimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Airtime model relationships.
     */
    public function testProgramRelationship(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $schedule = Airtime::factory()
            ->for($program, 'program')
            ->create();

        $this->assertTrue($schedule->program->is($program));
    }

    public function testResourceReturnsOriginalMidnightFormat(): void
    {
        $airtime = Airtime::factory()->make([
            'hour' => '00:00:00',
        ]);

        $resource = AirtimeResource::make($airtime)->resolve();

        $this->assertSame('00:00:00', $resource['hour']);
    }

    public function testResourceReturnsOriginalNoonFormat(): void
    {
        $airtime = Airtime::factory()->make([
            'hour' => '12:00:00',
        ]);

        $resource = AirtimeResource::make($airtime)->resolve();

        $this->assertSame('12:00:00', $resource['hour']);
    }
}
