<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PollOption;
use App\Models\Poll;

class PollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Poll::factory(5)
            ->has(PollOption::factory(4), 'options')
            ->create();
    }
}
