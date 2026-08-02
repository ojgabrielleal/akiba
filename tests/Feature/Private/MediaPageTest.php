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
        $user = $this->userWithPermissions([
            'media.module.view',
            'poll.list',
        ]);

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
                ->component('private/Media', false)
                ->where('latestPoll', null)
            );
    }

    public function test_guest_is_redirected_from_media_page(): void
    {
        $this
            ->get('/panel/media')
            ->assertRedirect('/panel');
    }

    public function test_media_page_requires_permission(): void
    {
        $this
            ->actingAs(User::factory()->create())
            ->get('/panel/media')
            ->assertForbidden();
    }

    public function test_media_page_renders_expected_component_for_authorized_user(): void
    {
        $user = $this->userWithPermissions([
            'media.module.view',
            'poll.list',
            'listener.gallery.list',
        ]);

        $this
            ->actingAs($user)
            ->get('/panel/media')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('private/Media', false)
                ->has('polls')
                ->has('listenerGalleries')
            );
    }

    private function userWithPermissions(array $permissionNames): User
    {
        $role = Role::factory()->create();
        $permissions = collect($permissionNames)
            ->map(fn (string $name) => Permission::factory()->create(['name' => $name]));
        $role->permissions()->attach($permissions);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
