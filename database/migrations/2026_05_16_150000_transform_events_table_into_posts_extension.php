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
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('post_id')
                ->nullable()
                ->after('uuid')
                ->constrained('posts')
                ->cascadeOnDelete();

            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'is_active',
                'user_id',
                'cover',
                'image',
                'slug',
                'title',
                'type',
                'content',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('uuid');
            $table->foreignId('user_id')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            $table->string('cover')->nullable()->after('user_id');
            $table->string('image')->nullable()->after('cover');
            $table->string('slug')->nullable()->after('image');
            $table->string('title')->nullable()->after('slug');
            $table->enum('type', ['published', 'revision', 'draft'])->nullable()->after('title');
            $table->longText('content')->nullable()->after('type');

            $table->dropForeign(['post_id']);
            $table->dropColumn('post_id');
        });
    }
};
