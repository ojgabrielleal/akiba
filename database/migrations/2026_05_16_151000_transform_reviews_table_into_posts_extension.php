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
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('post_id')
                ->nullable()
                ->after('uuid')
                ->constrained('posts')
                ->cascadeOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'is_active',
                'user_id',
                'slug',
                'cover',
                'image',
                'title',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('uuid');
            $table->string('slug')->nullable()->after('is_active');
            $table->string('cover')->nullable()->after('slug');
            $table->string('image')->nullable()->after('cover');
            $table->string('title')->nullable()->after('year_of_release');
            $table->foreignId('user_id')->nullable()->after('title')->constrained('users')->nullOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropColumn('post_id');
        });
    }
};
