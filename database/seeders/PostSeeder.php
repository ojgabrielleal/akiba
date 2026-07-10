<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Post;
use App\Models\PostReference;
use App\Models\PostReaction;
use App\Models\PostReview;
use App\Models\PostTag;

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
        $this->seedPosts($admin, 5);
        $this->seedReviews($admin, 5);
        $this->seedEvents($admin, 5);
    }

    private function seedNonAdministrationContent(User $user): void
    {
        $this->seedPosts($user, 15);
        $this->seedReviews($user, 5);
        $this->seedEvents($user, 5);
    }

    private function seedPosts(User $user, int $count): void
    {
        Post::factory($count)
            ->for($user, 'author')
            ->has(PostReference::factory(2), 'references')
            ->has(PostTag::factory(2), 'tags')
            ->has(PostReaction::factory(5), 'reactions')
            ->create();
    }

    private function seedReviews(User $user, int $count): void
    {
        Post::factory($count)
            ->review()
            ->for($user, 'author')
            ->afterCreating(fn (Post $post) => PostReview::factory(5)
                ->for($user, 'author')
                ->create(['post_id' => $post->id]))
            ->create();
    }

    private function seedEvents(User $user, int $count): void
    {
        Post::factory($count)
            ->event()
            ->for($user, 'author')
            ->create();
    }
}
