<?php

namespace Tests\Unit\Models;

use App\Services\ProgramService;
use App\Models\Onair;

use App\Models\Program;
use App\Models\ProgramAirtime;
use App\Models\ProgramSchedule;
use App\Models\User;
use App\Processing\ImageProcess;
use Database\Seeders\ProgramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Program model relationships.
     */
    public function test_host_relationship(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $this->assertTrue($program->host->is($user));
    }

    public function test_program_airtimes_relationship(): void
    {
        $user = User::factory()->create();
        $programAirtimes = ProgramAirtime::factory(3);

        $program = Program::factory()
            ->for($user, 'host')
            ->has($programAirtimes, 'programAirtimes')
            ->create();

        $this->assertCount(3, $program->programAirtimes);
        $this->assertContainsOnlyInstancesOf(ProgramAirtime::class, $program->programAirtimes);
    }

    public function test_onair_relationship(): void
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
    public function test_active_scope(): void
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

    public function test_available_for_locution_scope(): void
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

    public function test_factory_execution_mode_states(): void
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

        $autoDJ = Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->create();

        $this->assertSame('playlist', $playlist->execution_mode);
        $this->assertSame('scheduled', $scheduled->execution_mode);
        $this->assertSame('live', $live->execution_mode);
        $this->assertSame('auto_dj', $autoDJ->execution_mode);
    }

    public function test_factory_access_type_states(): void
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

    public function test_factory_provides_phrases_only_for_auto_dj_programs(): void
    {
        $user = User::factory()->create();

        $playlist = Program::factory()
            ->for($user, 'host')
            ->withPlaylist()
            ->create();

        $live = Program::factory()
            ->for($user, 'host')
            ->withLive()
            ->create();

        $scheduled = Program::factory()
            ->for($user, 'host')
            ->withScheduled()
            ->create();

        $autoDJ = Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->create();

        $this->assertIsArray($autoDJ->phrases);
        $this->assertNotEmpty($autoDJ->phrases);
        $this->assertNull($playlist->phrases);
        $this->assertNull($scheduled->phrases);
        $this->assertNull($live->phrases);
    }

    public function test_update_program_action_keeps_only_one_default_program(): void
    {
        $user = User::factory()->create();

        $currentDefault = Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->asDefault()
            ->create();

        $newDefault = Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->create();

        $service = new ProgramService(new ImageProcess);

        $service->update($newDefault, $user, $user, [
            'name' => $newDefault->name,
            'user' => $user->uuid,
            'access_type' => 'private',
            'execution_mode' => 'auto_dj',
            'is_default_auto_dj' => true,
            'phrases' => $newDefault->phrases,
            'schedules' => [],
        ]);

        $this->assertFalse($currentDefault->refresh()->is_default_auto_dj);
        $this->assertTrue($newDefault->refresh()->is_default_auto_dj);
    }

    public function test_program_seeder_creates_program_airtimes_for_live_programs(): void
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
        $autoDJ = Program::where('execution_mode', 'auto_dj')->first();

        $this->assertNotEmpty($livePrograms);
        $this->assertNotNull($scheduled);
        $this->assertNotNull($playlist);
        $this->assertNotNull($autoDJ);
        $this->assertTrue($livePrograms->every(fn (Program $program) => $program->programAirtimes()->exists()));
        $this->assertFalse($playlist->programAirtimes()->exists());
        $this->assertTrue($livePrograms->every(fn (Program $program) => $program->phrases === null));
        $this->assertNull($scheduled->phrases);
        $this->assertNull($playlist->phrases);
        $this->assertNotEmpty($autoDJ->phrases);
        $this->assertTrue($autoDJ->is_default_auto_dj);
    }

    public function test_update_program_action_deletes_program_schedules_when_submitted_empty(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->withPrivate()
            ->withScheduled()
            ->create();

        $startProgramSchedule = $program->schedules()->create([
            'user_id' => $user->id,
            'action' => 'start_program',
            'scheduled_at' => now()->addHour(),
            'status' => 'pending',
        ]);

        $service = new ProgramService(new ImageProcess);

        $service->update($program, $user, $user, [
            'name' => $program->name,
            'user' => $user->uuid,
            'access_type' => 'private',
            'execution_mode' => 'scheduled',
            'schedules' => [],
        ]);

        $this->assertFalse($program->schedules()->whereKey($startProgramSchedule)->exists());
        $this->assertDatabaseCount((new ProgramSchedule)->getTable(), 0);
    }
}
