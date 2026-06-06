<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Activity;
use App\Models\Calendar;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Calendar model relationships.
     */
    public function testResponsibleRelationship(): void
    {
        $user = User::factory()->create();

        $calendar = Calendar::factory()
            ->for($user, 'responsible')
            ->create();

        $this->assertTrue($calendar->responsible->is($user));
    }

    public function testActivityRelationship(): void
    {
        $user = User::factory()->create();

        $activity = Activity::factory()
            ->for($user, 'author')
            ->create();

        $calendar = Calendar::factory()
            ->for($user, 'responsible')
            ->for($activity, 'activity')
            ->create();

        $this->assertTrue($calendar->activity->is($activity));
    }

    /**
    * Tests from Calendar model scopes.
    */
    public function testValidScope(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('The valid scope uses MySQL-specific TIMESTAMP() and NOW() functions.');
        }

        $user = User::factory()->create();

        $futureCalendar = Calendar::factory()
            ->for($user, 'responsible')
            ->create([
                'date' => now()->addDay()->format('Y-m-d'),
                'hour' => now()->addDay()->format('H:i:s'),
            ]);

        $pastCalendar = Calendar::factory()
            ->for($user, 'responsible')
            ->create([
                'date' => now()->subDay()->format('Y-m-d'),
                'hour' => now()->subDay()->format('H:i:s'),
            ]);

        $calendars = Calendar::valid()->get();

        $this->assertTrue($calendars->contains($futureCalendar));
        $this->assertFalse($calendars->contains($pastCalendar));
    }
}
