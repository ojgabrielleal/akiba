<?php

namespace Database\Seeders;

use App\Models\ListenerGallery;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListenerGallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->firstOrFail();

        ListenerGallery::factory(5)
            ->for($user)
            ->create();
    }
}
