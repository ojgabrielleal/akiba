<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->nullableMorphs('commentable');
        });

        DB::table('post_comments')
            ->update([
                'commentable_type' => Post::class,
                'commentable_id' => DB::raw('post_id'),
            ]);

        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropColumn('post_id');
        });

        Schema::rename('post_comments', 'comments');
    }

    public function down(): void
    {
        Schema::rename('comments', 'post_comments');

        Schema::table('post_comments', function (Blueprint $table) {
            $table->foreignId('post_id')
                ->nullable()
                ->after('uuid')
                ->constrained('posts')
                ->cascadeOnDelete();
        });

        DB::table('post_comments')
            ->where('commentable_type', Post::class)
            ->update(['post_id' => DB::raw('commentable_id')]);

        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropMorphs('commentable');
        });
    }
};
