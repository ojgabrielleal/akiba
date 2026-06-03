<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Onair;
use App\Models\Program;
use App\Models\Airtime;
use Database\Seeders\ProgramSeeder;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Program model relationships.
     */
    public function testHostRelationship(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $this->assertTrue($program->host->is($user));
    }

    public function testSchedulesRelationship(): void
    {
        $user = User::factory()->create();
        $schedules = Airtime::factory(3);

        $program = Program::factory()
            ->for($user, 'host')
            ->has($schedules, 'schedules')
            ->create();

        $this->assertCount(3, $program->schedules);
        $this->assertContainsOnlyInstancesOf(Airtime::class, $program->schedules);
    }

    public function testOnairRelationship(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $onair = Onair::factory()
            ->for($program, 'program')
            ->create();

        $this->assertContainsOnlyInstancesOf(Onair::class, $program->onair);
    }

    /**
     * Tests from Program model scopes.
     */
    public function testActiveScope(): void
    {
        $user = User::factory()->create();

        $activeProgram = Program::factory()
            ->for($user, 'host')
            ->create(['is_active' => true]);

        $inactiveProgram = Program::factory()
            ->for($user, 'host')
            ->create(['is_active' => false]);

        $activePrograms = Program::active()->get();

        $this->assertTrue($activePrograms->contains($activeProgram));
        $this->assertFalse($activePrograms->contains($inactiveProgram));
    }

    public function testAvailableForLocutionScope(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownPrivateLive = Program::factory()
            ->for($user, 'host')
            ->withPrivate()
            ->create();

        $freeLive = Program::factory()
            ->for($otherUser, 'host')
            ->withFree()
            ->create();

        $otherPrivateLive = Program::factory()
            ->for($otherUser, 'host')
            ->withPrivate()
            ->create();

        $ownPrivatePlaylist = Program::factory()
            ->for($user, 'host')
            ->withPrivate()
            ->create(['execution_mode' => 'playlist']);

        $freeScheduled = Program::factory()
            ->for($otherUser, 'host')
            ->withFree()
            ->create(['execution_mode' => 'scheduled']);

        $inactiveOwnPrivateLive = Program::factory()
            ->for($user, 'host')
            ->withPrivate()
            ->create(['is_active' => false]);

        $programs = Program::availableForLocution($user)->get();

        $this->assertTrue($programs->contains($ownPrivateLive));
        $this->assertTrue($programs->contains($freeLive));
        $this->assertFalse($programs->contains($otherPrivateLive));
        $this->assertFalse($programs->contains($ownPrivatePlaylist));
        $this->assertFalse($programs->contains($freeScheduled));
        $this->assertFalse($programs->contains($inactiveOwnPrivateLive));
    }

    public function testFactoryExecutionModeStates(): void
    {
        $user = User::factory()->create();

        $playlist = Program::factory()
            ->for($user, 'host')
            ->withPlaylist()
            ->create();

        $scheduled = Program::factory()
            ->for($user, 'host')
            ->withScheduled()
            ->create();

        $live = Program::factory()
            ->for($user, 'host')
            ->withLive()
            ->create();

        $this->assertSame('playlist', $playlist->execution_mode);
        $this->assertSame('scheduled', $scheduled->execution_mode);
        $this->assertSame('live', $live->execution_mode);
    }

    public function testFactoryAccessTypeStates(): void
    {
        $user = User::factory()->create();

        $free = Program::factory()
            ->for($user, 'host')
            ->withFree()
            ->create();

        $private = Program::factory()
            ->for($user, 'host')
            ->withPrivate()
            ->create();

        $this->assertSame('free', $free->access_type);
        $this->assertSame('private', $private->access_type);
    }

    public function testFactoryProvidesPhrases(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $this->assertIsArray($program->phrases);
        $this->assertNotEmpty($program->phrases);
    }

    public function testProgramSeederCreatesAirtimesForLivePrograms(): void
    {
        User::factory()->create(['id' => 1]);
        User::factory()->create([
            'is_virtual' => false,
        ]);
        User::factory()->create([
            'is_virtual' => true,
        ]);

        $this->seed(ProgramSeeder::class);

        $livePrograms = Program::where('execution_mode', 'live')->get();
        $scheduled = Program::where('execution_mode', 'scheduled')->first();
        $playlist = Program::where('execution_mode', 'playlist')->first();

        $this->assertNotEmpty($livePrograms);
        $this->assertNotNull($scheduled);
        $this->assertNotNull($playlist);
        $this->assertTrue($livePrograms->every(fn (Program $program) => $program->schedules()->exists()));
        $this->assertFalse($playlist->schedules()->exists());
    }
}
