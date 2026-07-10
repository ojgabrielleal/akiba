<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Repository;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Repository model scopes.
     */
    public function testActiveScope(): void
    {
        $activeRepository = Repository::factory()
            ->create(['is_active' => true]);

        $inactiveRepository = Repository::factory()
            ->create(['is_active' => false]);

        $activeRepositories = Repository::active()->get();

        $this->assertTrue($activeRepositories->contains($activeRepository));
        $this->assertFalse($activeRepositories->contains($inactiveRepository));
        
    }
}
