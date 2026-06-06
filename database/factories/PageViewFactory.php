<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageView>
 */
class PageViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'viewable_type' => Post::class,
            'viewable_id' => Post::factory(),
            'ip_address' => fake()->ipv4(),
            'page_name' => fake()->words(3, true),
            'page_url' => fake()->url(),
        ];
    }
}
