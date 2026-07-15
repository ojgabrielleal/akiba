<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SyncAdministratorAndDeveloperPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = Permission::query()->pluck('id');

        Role::query()
            ->whereIn('name', ['administrador', 'desenvolvedor'])
            ->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($permissions));
    }
}
