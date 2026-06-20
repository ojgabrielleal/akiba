<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('listener_month', function (Blueprint $table) {
            $table->renameColumn('favorite_anime', 'favorite_music');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listener_month', function (Blueprint $table) {
            $table->renameColumn('fvorite_music', 'favorite_anime');
        });
    }
};
