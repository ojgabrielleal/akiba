<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Role model relationships.
     */
    public function testPermissionsRelationship(): void
    {
        $permission = Permission::factory(3);

        $role = Role::factory()
            ->has($permission, 'permissions')
            ->create();

        $this->assertCount(3, $role->permissions);
        $this->assertContainsOnlyInstancesOf(Permission::class, $role->permissions);
    }

    public function testMembersRelationship(): void
    {
        $role = Role::factory()->create();

        User::factory()
            ->hasAttached($role, [], 'roles')
            ->create();

        $this->assertCount(1, $role->members);
        $this->assertContainsOnlyInstancesOf(User::class, $role->members);
    }

    /**
     * Tests from Role model attributes.
     */
    public function testNameAttribute(): void
    {
        $role = Role::factory()->create([
            'label' => 'Editor Chefe',
        ]);

        $this->assertEquals('editor-chefe', $role->name);
    }
}
