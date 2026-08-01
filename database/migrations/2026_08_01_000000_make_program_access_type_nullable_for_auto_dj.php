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
        DB::statement("ALTER TABLE programs MODIFY access_type ENUM('free', 'private') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('programs')
            ->whereNull('access_type')
            ->update(['access_type' => 'private']);

        DB::statement("ALTER TABLE programs MODIFY access_type ENUM('free', 'private') NOT NULL");
    }
};
