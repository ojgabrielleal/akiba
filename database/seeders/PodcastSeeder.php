<?php

namespace Database\Seeders;

use App\Models\Podcast;
use App\Models\User;
use Illuminate\Database\Seeder;

class PodcastSeeder extends Seeder
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
        Podcast::factory(5)
            ->for($admin, 'author')
            ->create();
    }

    private function seedNonAdministrationContent(User $user): void
    {
        Podcast::factory(5)
            ->for($user, 'author')
            ->create();
    }
}
