<?php

namespace Tests\Feature\Private;

use App\Models\Onair;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrashPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => str_repeat('a', 32)]);
    }

    public function test_page_lists_only_trash_items(): void
    {
        $user = $this->userWithPermissions([
            'trash.module.view',
            'trash.restore',
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
            ->get('/panel/trash')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('trash_items', 2)
                ->where('trash_items.0.uuid', $inactivePost->uuid)
                ->where('trash_items.0.type', 'post')
                ->where('trash_items.1.uuid', $inactiveProgram->uuid)
                ->where('trash_items.1.type', 'program')
            );
    }

    public function test_guest_is_redirected_from_trash_items_page(): void
    {
        $this
            ->get('/panel/trash')
            ->assertRedirect('/panel');
    }

    public function test_trash_items_page_requires_permission(): void
    {
        $this
            ->actingAs(User::factory()->create())
            ->get('/panel/trash')
            ->assertForbidden();
    }

    public function test_trash_items_page_renders_expected_component(): void
    {
        $user = $this->userWithPermissions(['trash.module.view']);

        $this
            ->actingAs($user)
            ->get('/panel/trash')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('private/Trash', false)
                ->has('trash_items')
            );
    }

    public function test_user_can_reactivate_a_trash_item(): void
    {
        $user = $this->userWithPermissions([
            'trash.module.view',
            'trash.restore',
        ]);
        $program = Program::factory()
            ->for(User::factory(), 'host')
            ->create(['is_active' => false]);

        $this
            ->actingAs($user)
            ->patch("/panel/trash/program/{$program->uuid}/reactivate")
            ->assertRedirect();

        $this->assertTrue($program->fresh()->is_active);
    }

    public function test_restore_permission_is_required(): void
    {
        $user = $this->userWithPermissions(['trash.module.view']);
        $program = Program::factory()
            ->for(User::factory(), 'host')
            ->create(['is_active' => false]);

        $this
            ->actingAs($user)
            ->patch("/panel/trash/program/{$program->uuid}/reactivate")
            ->assertForbidden();

        $this->assertFalse($program->fresh()->is_active);
    }

    public function test_user_can_permanently_delete_a_trash_item(): void
    {
        $user = $this->userWithPermissions([
            'trash.module.view',
            'trash.delete',
        ]);
        $program = Program::factory()
            ->for(User::factory(), 'host')
            ->create(['is_active' => false]);
        $onair = Onair::factory()->for($program, 'program')->create();

        $this
            ->actingAs($user)
            ->delete("/panel/trash/program/{$program->uuid}")
            ->assertRedirect();

        $this->assertModelMissing($program);
        $this->assertModelMissing($onair);
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
