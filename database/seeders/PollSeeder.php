<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class PollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->firstOrFail();

        Poll::factory(5)
            ->for($user)
            ->has(PollOption::factory(4), 'options')
            ->create();
    }
}
