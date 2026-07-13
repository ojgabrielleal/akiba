<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrFail();

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
