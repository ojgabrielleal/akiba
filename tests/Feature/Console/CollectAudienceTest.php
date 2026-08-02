<?php

namespace Tests\Feature\Console;

use App\Services\Process\AudienceCollectorService;
use Mockery;
use Tests\TestCase;

class CollectAudienceTest extends TestCase
{
    public function test_it_collects_audience_and_returns_success(): void
    {
        $collector = Mockery::mock(AudienceCollectorService::class);
        $collector
            ->shouldReceive('collect')
            ->once();

        $this->app->instance(AudienceCollectorService::class, $collector);

        $this
            ->artisan('audience:collect')
            ->expectsOutput('Radio audience collected successfully.')
            ->assertSuccessful();
    }
}
