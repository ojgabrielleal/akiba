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
            $table->foreignId('paused_plan_id')
                ->nullable()
                ->after('program_id')
                ->constrained('plans')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onair', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paused_plan_id');
        });
    }
};
