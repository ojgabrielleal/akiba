<?php

namespace Database\Factories;

use App\Models\User;
use Database\Factories\Concerns\HasFakeImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    use HasFakeImages;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_active' => true,
            'user_id' => User::factory(),
            'image' => $this->fakeImageUrl(),
            'title' => fake()->text(),
            'content' => fake()->paragraph(),
            'cover' => $this->fakeImageUrl(),
            'status' => fake()->randomElement(['published', 'revision', 'draft']),
            'module' => 'post',
            'metadata' => null,
        ];
    }

    public function review(): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => 'review',
            'content' => null,
            'status' => 'published',
            'metadata' => [
                'year_of_release' => fake()->year(),
                'sinopse' => fake()->paragraph(),
            ],
        ]);
    }

    public function event(): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => 'event',
            'metadata' => [
                'dates' => fake()->date(),
                'address' => fake()->address(),
            ],
        ]);
    }
}
