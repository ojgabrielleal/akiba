<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Option;
use App\Models\Poll;

class PollTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Poll model relationships.
     */
    public function testOptionsRelationship(): void
    {
        $options = Option::factory(3);

        $poll = Poll::factory()
            ->has($options, 'options')
            ->create();

        $firstOption = $poll->options->first();

        $this->assertCount(3, $poll->options);
        $this->assertContainsOnlyInstancesOf(Option::class, $poll->options);
        $this->assertNotNull($firstOption);
        $this->assertTrue($firstOption->poll->is($poll));
    }

    public function testOptionUsesOptionsTable(): void
    {
        $this->assertSame('options', (new Option())->getTable());
    }

    /**
     * Tests from Poll model scopes.
     */
    public function testActiveScope(): void
    {
        $activePoll = Poll::factory()
            ->create([
                'is_active' => true
            ]); 

        $inactivePoll = Poll::factory()
            ->create([
                'is_active' => false
            ]);

        $polls = Poll::active()->get();

        $this->assertTrue($polls->contains($activePoll));
        $this->assertFalse($polls->contains($inactivePoll));
    }
}
