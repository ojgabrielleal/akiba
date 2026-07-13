<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrFail();
        $user = User::whereKeyNot($admin->id)->inRandomOrder()->firstOrFail();

        $this->seedAdministration($admin);
        $this->seedNonAdministrationContent($user);
    }

    private function seedAdministration(User $admin): void
    {
        Task::factory(5)
            ->for($admin, 'responsible')
            ->create();

        Task::factory(5)
            ->for($admin, 'responsible')
            ->withDeadline()
            ->create();
    }

    private function seedNonAdministrationContent(User $user): void
    {
        Task::factory(5)
            ->for($user, 'responsible')
            ->create();
    }
}
