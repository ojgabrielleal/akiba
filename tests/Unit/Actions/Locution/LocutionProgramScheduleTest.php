<?php

namespace Tests\Unit\Actions\Locution;

use App\Actions\Locution\FinishLocutionAction;
use App\Actions\Locution\StartLocutionAction;
use App\Models\Onair;
use App\Models\Program;
use App\Models\ProgramSchedule;
use App\Models\User;
use App\Services\External\DiscordWebhookService;
use App\Services\External\OneSignalService;
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

        $startAction = new StartLocutionAction(new DiscordWebhookService, new OneSignalService);

        $startAction->execute($user, $program, [
            'phrase' => [
                'text' => 'Ao vivo',
                'icon' => null,
                'decoration' => 'default',
                'texture' => null,
            ],
            'send_notification' => false,
        ]);

        $this->assertSame('completed', $completedSchedule->refresh()->status);

        (new FinishLocutionAction)->execute();

        $this->assertSame('completed', $completedSchedule->refresh()->status);
    }
}
