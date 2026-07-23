<?php

namespace Tests\Feature\Private;

use App\Models\Permission;
use App\Models\Post;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveItemsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => str_repeat('a', 32)]);
    }

    public function test_page_lists_only_inactive_items(): void
    {
        $user = $this->userWithPermissions([
            'inactive.module.view',
            'inactive.restore',
        ]);

        $host = User::factory()->create();
        $inactiveProgram = Program::factory()->for($host, 'host')->create([
            'is_active' => false,
            'name' => 'Programa arquivado',
        ]);
        Program::factory()->for($host, 'host')->create([
            'is_active' => true,
            'name' => 'Programa ativo',
        ]);
        $inactivePost = Post::factory()->create([
            'is_active' => false,
            'title' => 'Matéria arquivada',
        ]);

        $this
            ->actingAs($user)
            ->get('/panel/inactive')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('inactive_items', 2)
                ->where('inactive_items.0.uuid', $inactivePost->uuid)
                ->where('inactive_items.0.type', 'post')
                ->where('inactive_items.1.uuid', $inactiveProgram->uuid)
                ->where('inactive_items.1.type', 'program')
            );
    }

    public function test_user_can_reactivate_an_inactive_item(): void
    {
        $user = $this->userWithPermissions([
            'inactive.module.view',
            'inactive.restore',
        ]);
        $program = Program::factory()
            ->for(User::factory(), 'host')
            ->create(['is_active' => false]);

        $this
            ->actingAs($user)
            ->patch("/panel/inactive/program/{$program->uuid}/reactivate")
            ->assertRedirect();

        $this->assertTrue($program->fresh()->is_active);
    }

    public function test_restore_permission_is_required(): void
    {
        $user = $this->userWithPermissions(['inactive.module.view']);
        $program = Program::factory()
            ->for(User::factory(), 'host')
            ->create(['is_active' => false]);

        $this
            ->actingAs($user)
            ->patch("/panel/inactive/program/{$program->uuid}/reactivate")
            ->assertForbidden();

        $this->assertFalse($program->fresh()->is_active);
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
