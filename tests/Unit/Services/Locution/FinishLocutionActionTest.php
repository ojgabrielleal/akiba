<?php

namespace Tests\Unit\Services\Locution;

use App\Models\Onair;
use App\Models\Program;
use App\Models\User;
use App\Services\LocutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinishLocutionActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_starts_default_auto_dj_when_finishing_locution(): void
    {
        $user = User::factory()->create();

        $liveProgram = Program::factory()
            ->for($user, 'host')
            ->withLive()
            ->create();

        Onair::factory()
            ->for($liveProgram, 'program')
            ->live()
            ->create();

        Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->create();

        $defaultAutoDj = Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->asDefault()
            ->create();

        app(LocutionService::class)->finish();

        $onair = Onair::live()->first();

        $this->assertTrue($onair->program->is($defaultAutoDj));
        $this->assertSame('auto_dj', $onair->execution_mode);
    }
}
