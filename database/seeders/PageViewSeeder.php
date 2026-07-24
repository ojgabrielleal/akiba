<?php

namespace Database\Seeders;

use App\Models\PageView;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PageViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::query()
            ->forModule('post')
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->each(function (Post $post): void {
                PageView::factory(fake()->numberBetween(10, 100))
                    ->for($post, 'viewable')
                    ->create();
            });
    }
}
