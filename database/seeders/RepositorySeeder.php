<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Repository;

class RepositorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Repository::factory(5)->create();
    }
}
