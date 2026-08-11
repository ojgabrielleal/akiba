<?php

namespace Tests\Unit\Services\Locution;

use App\Services\LocutionService;
use App\Models\Onair;
use App\Models\Program;
use App\Models\ProgramSchedule;
use App\Models\User;
use App\Integrations\DiscordWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocutionProgramScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_and_finishing_locution_does_not_change_schedule_status(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->withLive()
            ->create();

        Onair::factory()
            ->for($program, 'program')
            ->create();

        $completedSchedule = ProgramSchedule::factory()->create([
            'action' => 'start_program',
            'status' => 'completed',
        ]);

        $startService = new LocutionService(new DiscordWebhookService);

        $startService->start($user, $program, [
            'phrase' => [
                'text' => 'Ao vivo',
                'icon' => null,
                'decoration' => 'default',
                'texture' => null,
            ],
            'send_notification' => false,
        ]);

        $this->assertSame('completed', $completedSchedule->refresh()->status);

        app(LocutionService::class)->finish();

        $this->assertSame('completed', $completedSchedule->refresh()->status);
    }
}
