<?php

namespace Tests\Unit\Services;

use App\Models\Onair;
use App\Models\Program;
use App\Models\RadioStation;
use App\Models\User;
use App\Integrations\AudienceService;
use App\Processing\AudienceCollectorProcess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AudienceCollectorProcessTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_only_a_higher_peak_for_the_current_program(): void
    {
        $station = RadioStation::query()->create([
            'name' => 'Rádio Akiba',
            'logo' => '/img/brand/logo.webp',
            'website' => 'https://akiba.test',
            'endpoint' => 'https://stream.akiba.test',
            'listeners_path' => 'listeners',
            'is_active' => true,
        ]);

        $program = Program::factory()
            ->for(User::factory(), 'host')
            ->create();
        $onair = Onair::factory()
            ->scheduled()
            ->for($program, 'program')
            ->create(['peak_listeners' => 10]);
        $autoDj = Onair::factory()
            ->autoDj()
            ->for(Program::factory()->for(User::factory(), 'host'), 'program')
            ->create(['peak_listeners' => 0]);

        $audienceService = Mockery::mock(AudienceService::class);
        $audienceService->shouldReceive('get')
            ->twice()
            ->with(Mockery::on(fn (RadioStation $item) => $item->is($station)))
            ->andReturn(
                ['listeners' => 42, 'status' => 'online', 'response_time_ms' => 20],
                ['listeners' => 30, 'status' => 'online', 'response_time_ms' => 18],
            );

        $collector = new AudienceCollectorProcess($audienceService);
        $collector->collect();

        $firstPeakAt = $onair->fresh()->peak_listeners_at;

        $this->assertSame(42, $onair->fresh()->peak_listeners);
        $this->assertSame(0, $autoDj->fresh()->peak_listeners);
        $this->assertNotNull($firstPeakAt);

        $collector->collect();

        $this->assertSame(42, $onair->fresh()->peak_listeners);
        $this->assertTrue($firstPeakAt->equalTo($onair->fresh()->peak_listeners_at));
        $this->assertCount(2, $station->audienceSnapshots()->get());
    }
}
