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
        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('type', 'status');
        });

        Schema::table('opinions', function (Blueprint $table) {
            $table->renameColumn('type', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opinions', function (Blueprint $table) {
            $table->renameColumn('status', 'type');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('status', 'type');
        });
    }
};
