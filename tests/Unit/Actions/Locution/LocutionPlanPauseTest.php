<?php

namespace Tests\Unit\Actions\Locution;

use App\Actions\Locution\FinishLocutionAction;
use App\Actions\Locution\StartLocutionAction;
use App\Models\Onair;
use App\Models\Plan;
use App\Models\Program;
use App\Models\User;
use App\Services\External\DiscordWebhookService;
use App\Services\External\OneSignalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocutionPlanPauseTest extends TestCase
{
    use RefreshDatabase;

    public function testFinishLocutionResumesOnlyPlanPausedByCurrentLocution(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->withLive()
            ->create();

        Onair::factory()
            ->for($program, 'program')
            ->create();

        $runningPlan = Plan::factory()->create([
            'action' => 'start_program',
            'status' => 'running',
        ]);

        $alreadyPausedPlan = Plan::factory()->create([
            'action' => 'start_program',
            'status' => 'paused',
        ]);

        $startAction = new StartLocutionAction(new DiscordWebhookService(), new OneSignalService());

        $startAction->execute($user, $program, [
            'phrase' => [
                'text' => 'Ao vivo',
                'icon' => null,
                'decoration' => 'default',
                'texture' => null,
            ],
            'send_notification' => false,
        ]);

        $liveOnair = Onair::live()->first();

        $this->assertSame($runningPlan->id, $liveOnair->paused_plan_id);
        $this->assertSame('paused', $runningPlan->refresh()->status);
        $this->assertSame('paused', $alreadyPausedPlan->refresh()->status);

        (new FinishLocutionAction())->execute();

        $this->assertSame('running', $runningPlan->refresh()->status);
        $this->assertSame('paused', $alreadyPausedPlan->refresh()->status);
    }
}
