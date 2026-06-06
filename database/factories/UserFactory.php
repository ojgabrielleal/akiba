<?php

namespace Database\Factories;

use App\Models\Favority;
use App\Models\Preference;
use App\Models\Role;
use App\Models\Social;
use App\Models\User;
use Database\Factories\Concerns\HasFakeImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    use HasFakeImages;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->attributes['gender'] ?? fake()->randomElement(['male', 'female']);

        return [
            'is_active' => true,
            'is_virtual' => false,
            'slug' => fake()->slug(),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'name' => fake()->name(),
            'nickname' => fake()->userName(),
            'gender' => $gender,
            'avatar' => $this->avatarForGender($gender),
            'birthday' => fake()->date(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
            'bibliography' => fake()->paragraph(),
        ];
    }

    public function withAdministrator(): static
    {
        return $this
            ->state(fn (array $attributes) => [
                'username' => 'admin',
                'password' => 'admin',
                'name' => 'Yagami Kou',
                'nickname' => 'Yagami',
                'gender' => 'female',
                'avatar' => $this->avatarForGender('female'),
            ])
            ->afterCreating(function (User $user) {
                $administrator = Role::where('name', 'administrator')->first();

                if ($administrator) {
                    $user->roles()->syncWithoutDetaching([$administrator->id]);
                }
            });
    }

    public function withVirtual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_virtual' => true,
            'username' => null,
            'password' => null,
        ]);
    }

    public function withRole(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', '!=', 'administrator')
                ->inRandomOrder()
                ->first();

            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        });
    }

    public function withDefaults(): static
    {
        return $this->afterCreating(function (User $user) {
            if (! $user->socials()->exists()) {
                foreach ($this->socials() as $social) {
                    $user->socials()->save(Social::factory()->make($social));
                }
            }

            if (! $user->preferences()->exists()) {
                foreach ($this->preferences() as $preference) {
                    $user->preferences()->save(Preference::factory()->make($preference));
                }
            }

            if (! $user->favorites()->exists()) {
                foreach ($this->favorites() as $favorite) {
                    $user->favorites()->save(Favority::factory()->make($favorite));
                }
            }
        });
    }

    private function socials(): array
    {
        return [
            ['name' => 'Facebook', 'url' => null],
            ['name' => 'Instagram', 'url' => null],
            ['name' => 'Twitter', 'url' => null],
            ['name' => 'Bluesky', 'url' => null],
            ['name' => 'Discord', 'url' => null],
            ['name' => 'YouTube', 'url' => null],
            ['name' => 'MyAnimeList', 'url' => null],
        ];
    }

    private function preferences(): array
    {
        return [
            ['is_like' => true, 'content' => '#'],
            ['is_like' => true, 'content' => '#'],
            ['is_like' => true, 'content' => '#'],
            ['is_like' => false, 'content' => '#'],
            ['is_like' => false, 'content' => '#'],
            ['is_like' => false, 'content' => '#'],
        ];
    }

    private function favorites(): array
    {
        return [
            ['name' => null, 'image' => null],
            ['name' => null, 'image' => null],
            ['name' => null, 'image' => null],
        ];
    }

    private function avatarForGender(string $gender): string
    {
        return $gender === 'male'
            ? '/img/defaults/user-male.webp'
            : '/img/defaults/user-female.webp';
    }
}
