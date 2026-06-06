<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('reviews_contents', 'opinions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('opinions', 'reviews_contents');
    }
};
