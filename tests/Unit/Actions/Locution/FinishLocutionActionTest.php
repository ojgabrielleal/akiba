<?php

namespace Tests\Unit\Actions\Locution;

use App\Actions\Locution\FinishLocutionAction;
use App\Models\Onair;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinishLocutionActionTest extends TestCase
{
    use RefreshDatabase;

    public function testItStartsDefaultAutoDjWhenFinishingLocution(): void
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

        app(FinishLocutionAction::class)->execute();

        $onair = Onair::live()->first();

        $this->assertTrue($onair->program->is($defaultAutoDj));
        $this->assertSame('auto_dj', $onair->execution_mode);
    }
}
