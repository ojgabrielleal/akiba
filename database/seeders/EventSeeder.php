<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Event;
use App\Models\Post;
use App\Models\User;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::find(1);
        $user = User::inRandomOrder()->first();

        $this->seedAdministration($admin);
        $this->seedNonAdministrationContent($user);
    }

    private function seedAdministration(User $admin): void
    {
        Post::factory(5)
            ->for($admin, 'author')
            ->has(Event::factory(), 'event')
            ->create();
    }

    private function seedNonAdministrationContent(User $user): void
    {
        Post::factory(5)
            ->for($user, 'author')
            ->has(Event::factory(), 'event')
            ->create();
    }
}
