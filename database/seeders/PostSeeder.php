<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Post;
use App\Models\Reference;
use App\Models\Reaction;
use App\Models\Tag;

class PostSeeder extends Seeder
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
            ->has(Reference::factory(2), 'references')
            ->has(Tag::factory(2), 'tags')
            ->has(Reaction::factory(5), 'reactions')
            ->create();
    }

    private function seedNonAdministrationContent(User $user): void
    {
        Post::factory(15)
            ->for($user, 'author')
            ->has(Reference::factory(2), 'references')
            ->has(Tag::factory(2), 'tags')
            ->has(Reaction::factory(5), 'reactions')
            ->create();
    }
}
