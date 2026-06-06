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
        Schema::table('reviews_contents', function (Blueprint $table) {
            $table->enum('type', ['published', 'revision', 'draft'])->after('review_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews_contents', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
