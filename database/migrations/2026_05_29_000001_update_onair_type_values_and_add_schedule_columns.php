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
        Schema::table('onair', function (Blueprint $table) {
            $table->enum('type', ['auto_dj', 'live', 'scheduled', 'playlist'])->change();
            $table->datetime('start_at')->nullable()->after('type');
            $table->datetime('finish_at')->nullable()->after('start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onair', function (Blueprint $table) {
            $table->dropColumn(['start_at', 'finish_at']);
            $table->enum('type', ['automatic', 'live', 'scheduled'])->change();
        });
    }
};
