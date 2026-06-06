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
        Schema::rename('posts_references', 'references');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('references', 'posts_references');
    }
};
