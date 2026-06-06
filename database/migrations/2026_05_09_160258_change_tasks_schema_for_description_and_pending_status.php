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
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('content', 'description');
            $table->string('description')->change();
            $table->enum('status', ['pending', 'in_review', 'completed'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status', ['in_progress', 'in_review', 'completed', 'late'])->change();
            $table->renameColumn('description', 'content');
            $table->longText('description')->change();
        });
    }
};
