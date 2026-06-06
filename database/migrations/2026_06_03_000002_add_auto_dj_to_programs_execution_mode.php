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
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE programs MODIFY execution_mode ENUM('playlist', 'scheduled', 'live', 'auto_dj') NOT NULL DEFAULT 'live'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('programs')
            ->where('execution_mode', 'auto_dj')
            ->update(['execution_mode' => 'playlist']);

        DB::statement("ALTER TABLE programs MODIFY execution_mode ENUM('playlist', 'scheduled', 'live') NOT NULL DEFAULT 'live'");
    }
};
