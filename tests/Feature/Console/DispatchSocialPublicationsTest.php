<?php

namespace Tests\Feature\Console;

use App\Jobs\PublishSocialPublicationSchedule;
use App\Models\SocialPublicationSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DispatchSocialPublicationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_only_due_pending_social_publications(): void
    {
        Queue::fake();
        $due = SocialPublicationSchedule::factory()->create([
            'status' => SocialPublicationSchedule::STATUS_PENDING,
            'scheduled_at' => now()->subMinute(),
        ]);
        SocialPublicationSchedule::factory()->create([
            'status' => SocialPublicationSchedule::STATUS_PENDING,
            'scheduled_at' => now()->addMinute(),
        ]);
        SocialPublicationSchedule::factory()->published()->create([
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('social-publications:dispatch-pending')
            ->expectsOutput('Social publication schedules dispatched: 1.')
            ->assertSuccessful();

        Queue::assertPushed(
            PublishSocialPublicationSchedule::class,
            fn (PublishSocialPublicationSchedule $job): bool => $job->scheduleId === $due->id,
        );
        Queue::assertPushed(PublishSocialPublicationSchedule::class, 1);
    }
}
