<?php

namespace Database\Factories;

use App\Models\User;
use Database\Factories\Concerns\HasFakeImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListenerGallery>
 */
class ListenerGalleryFactory extends Factory
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
            'user_id' => User::factory(),
            'image' => $this->fakeImageUrl(),
            'caption' => fake()->sentence(),
            'listener_name' => fake()->name(),
        ];
    }
}
