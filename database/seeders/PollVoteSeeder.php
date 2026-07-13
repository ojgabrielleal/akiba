<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollVote;
use App\Models\User;
use Illuminate\Database\Seeder;

class PollVoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()
            ->where('is_virtual', true)
            ->take(5)
            ->get();

        if ($users->count() < 5) {
            $users = $users->merge(
                User::factory(5 - $users->count())
                    ->withVirtual()
                    ->create()
            );
        }

        Poll::with('options')
            ->has('options')
            ->get()
            ->each(function (Poll $poll) use ($users) {
                $users->each(fn (User $user) => PollVote::factory()
                    ->for($poll)
                    ->for($poll->options->random(), 'option')
                    ->for($user)
                    ->create());
            });
    }
}
