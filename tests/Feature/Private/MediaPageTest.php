<?php

namespace Tests\Feature\Private;

use App\Models\Permission;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaPageTest extends TestCase
{
    use RefreshDatabase;

    public function testMediaPageRendersWhenThereIsNoLatestValidPoll(): void
    {
        $user = $this->userWithPermission('poll.list');

        Poll::factory()
            ->has(PollOption::factory(4), 'options')
            ->create([
                'is_active' => true,
                'expires_at' => now()->subDay(),
            ]);

        $this
            ->actingAs($user)
            ->get('/panel/media')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('private/Media')
                ->where('latestPoll', null)
            );
    }

    private function userWithPermission(string $permissionName): User
    {
        $permission = Permission::factory()->create([
            'name' => $permissionName,
        ]);

        $role = Role::factory()->create();
        $role->permissions()->attach($permission);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
