<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::find(1);

        $this->seedHasConfirmations($admin);
        $this->seedNotHasConfirmations($admin);
    }

    public function seedHasConfirmations(User $user): void
    {
        Activity::factory(5)
            ->withAllowsConfirmations()
            ->for($user, 'author')
            ->create();
    }

    public function seedNotHasConfirmations(User $user): void
    {
        Activity::factory(5)
            ->for($user, 'author')
            ->create();
    }
}
