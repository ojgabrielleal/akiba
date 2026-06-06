<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::find(1);
        $user = User::where('id', '!=', 1)->inRandomOrder()->first();

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
