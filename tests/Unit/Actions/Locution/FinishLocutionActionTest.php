<?php

namespace Tests\Unit\Services\Locution;

use App\Services\LocutionService;
use App\Models\Onair;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocutionServiceTest extends TestCase
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

        app(LocutionService::class)->finish();

        $onair = Onair::live()->first();

        $this->assertTrue($onair->program->is($defaultAutoDj));
        $this->assertSame('auto_dj', $onair->execution_mode);
    }
}
