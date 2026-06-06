<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Option;
use App\Models\Poll;

class PollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Poll::factory(5)
            ->has(Option::factory(4), 'options')
            ->create();
    }
}
