<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Opinion;
use App\Models\Review;
use App\Models\Post;

class ReviewSeeder extends Seeder
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
        $adminOpinions = Opinion::factory(5)
            ->for($admin, 'author');

        Post::factory(5)
            ->for($admin, 'author')
            ->has(
                Review::factory()
                    ->has($adminOpinions, 'opinions'),
                'review'
            )
            ->create();
    }

    private function seedNonAdministrationContent(User $user): void
    {
        $userOpinions = Opinion::factory(5)
            ->for($user, 'author');

        Post::factory(5)
            ->for($user, 'author')
            ->has(
                Review::factory()
                    ->has($userOpinions, 'opinions'),
                'review'
            )
            ->create();
    }
}
