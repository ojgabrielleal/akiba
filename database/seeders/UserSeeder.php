<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->withAdministrator()
            ->withDefaults()
            ->create();

        User::factory(5)
            ->withRole()
            ->withDefaults()
            ->create();

        User::factory()
            ->withVirtual()
            ->withDefaults()
            ->create([
                'name' => 'Auto DJ',
                'nickname' => 'Auto DJ',
                'gender' => 'female',
                'avatar' => '/img/defaults/user-female.webp',
            ]);
    }
}
