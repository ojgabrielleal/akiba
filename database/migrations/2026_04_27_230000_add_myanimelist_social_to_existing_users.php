<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = DB::table('users')->select('id')->get();

        foreach ($users as $user) {
            $hasMyAnimeList = DB::table('users_socials')
                ->where('user_id', $user->id)
                ->where('name', 'MyAnimeList')
                ->exists();

            if (!$hasMyAnimeList) {
                DB::table('users_socials')->insert([
                    'uuid' => (string) str()->uuid(),
                    'user_id' => $user->id,
                    'name' => 'MyAnimeList',
                    'url' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users_socials')
            ->where('name', 'MyAnimeList')
            ->whereNull('url')
            ->delete();
    }
};
