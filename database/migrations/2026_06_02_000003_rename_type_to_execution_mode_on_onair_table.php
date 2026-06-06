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
            $table->renameColumn('type', 'execution_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onair', function (Blueprint $table) {
            $table->renameColumn('execution_mode', 'type');
        });
    }
};
