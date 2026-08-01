<?php

use App\Models\OAuthAccount;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->nullableMorphs('voter');
        });

        DB::table('poll_votes')
            ->whereNotNull('user_id')
            ->update(['voter_type' => User::class, 'voter_id' => DB::raw('user_id')]);

        DB::table('poll_votes')
            ->whereNull('voter_id')
            ->whereNotNull('oauth_id')
            ->update(['voter_type' => OAuthAccount::class, 'voter_id' => DB::raw('oauth_id')]);

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropForeign(['oauth_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->index('poll_id', 'poll_votes_poll_id_index');
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique(['poll_id', 'oauth_id']);
            $table->dropUnique(['poll_id', 'user_id']);
            $table->dropColumn(['oauth_id', 'user_id']);
            $table->unique(['poll_id', 'voter_type', 'voter_id']);
        });

        Schema::table('song_requests', function (Blueprint $table) {
            $table->nullableMorphs('requester');
        });

        DB::table('song_requests')
            ->whereNotNull('oauth_account_id')
            ->update(['requester_type' => OAuthAccount::class, 'requester_id' => DB::raw('oauth_account_id')]);

        Schema::table('song_requests', function (Blueprint $table) {
            $table->dropForeign(['oauth_account_id']);
        });

        Schema::table('song_requests', function (Blueprint $table) {
            $table->dropColumn('oauth_account_id');
        });

        Schema::table('post_comments', function (Blueprint $table) {
            $table->nullableMorphs('author');
        });

        DB::table('post_comments')
            ->whereNotNull('oauth_account_id')
            ->update(['author_type' => OAuthAccount::class, 'author_id' => DB::raw('oauth_account_id')]);

        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropForeign(['oauth_account_id']);
        });

        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropColumn('oauth_account_id');
        });

        Schema::table('post_reactions', function (Blueprint $table) {
            $table->nullableMorphs('reactor');
        });

        DB::table('post_reactions')
            ->whereNotNull('oauth_account_id')
            ->update(['reactor_type' => OAuthAccount::class, 'reactor_id' => DB::raw('oauth_account_id')]);

        Schema::table('post_reactions', function (Blueprint $table) {
            $table->dropForeign(['oauth_account_id']);
        });

        Schema::table('post_reactions', function (Blueprint $table) {
            $table->index('post_id', 'post_reactions_post_id_index');
        });

        Schema::table('post_reactions', function (Blueprint $table) {
            $table->dropUnique(['post_id', 'oauth_account_id']);
            $table->dropColumn('oauth_account_id');
            $table->unique(['post_id', 'reactor_type', 'reactor_id']);
        });

        Schema::table('post_likes', function (Blueprint $table) {
            $table->nullableMorphs('liker');
        });

        DB::table('post_likes')
            ->whereNotNull('oauth_account_id')
            ->update(['liker_type' => OAuthAccount::class, 'liker_id' => DB::raw('oauth_account_id')]);

        Schema::table('post_likes', function (Blueprint $table) {
            $table->dropForeign(['oauth_account_id']);
        });

        Schema::table('post_likes', function (Blueprint $table) {
            $table->index('post_id', 'post_likes_post_id_index');
        });

        Schema::table('post_likes', function (Blueprint $table) {
            $table->dropUnique(['post_id', 'oauth_account_id']);
            $table->dropColumn('oauth_account_id');
            $table->unique(['post_id', 'liker_type', 'liker_id']);
        });
    }

    public function down(): void
    {
        Schema::table('post_likes', function (Blueprint $table) {
            $table->foreignId('oauth_account_id')
                ->nullable()
                ->after('post_id')
                ->constrained('oauth_accounts')
                ->cascadeOnDelete();
        });

        DB::table('post_likes')
            ->where('liker_type', OAuthAccount::class)
            ->update(['oauth_account_id' => DB::raw('liker_id')]);

        Schema::table('post_likes', function (Blueprint $table) {
            $table->dropUnique(['post_id', 'liker_type', 'liker_id']);
            $table->dropMorphs('liker');
            $table->dropIndex('post_likes_post_id_index');
            $table->unique(['post_id', 'oauth_account_id']);
        });

        Schema::table('post_reactions', function (Blueprint $table) {
            $table->foreignId('oauth_account_id')
                ->nullable()
                ->after('post_id')
                ->constrained('oauth_accounts')
                ->cascadeOnDelete();
        });

        DB::table('post_reactions')
            ->where('reactor_type', OAuthAccount::class)
            ->update(['oauth_account_id' => DB::raw('reactor_id')]);

        Schema::table('post_reactions', function (Blueprint $table) {
            $table->dropUnique(['post_id', 'reactor_type', 'reactor_id']);
            $table->dropMorphs('reactor');
            $table->dropIndex('post_reactions_post_id_index');
            $table->unique(['post_id', 'oauth_account_id']);
        });

        Schema::table('post_comments', function (Blueprint $table) {
            $table->foreignId('oauth_account_id')
                ->nullable()
                ->after('post_id')
                ->constrained('oauth_accounts')
                ->cascadeOnDelete();
        });

        DB::table('post_comments')
            ->where('author_type', OAuthAccount::class)
            ->update(['oauth_account_id' => DB::raw('author_id')]);

        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropMorphs('author');
        });

        Schema::table('song_requests', function (Blueprint $table) {
            $table->foreignId('oauth_account_id')
                ->nullable()
                ->after('music_id')
                ->constrained('oauth_accounts')
                ->nullOnDelete();
        });

        DB::table('song_requests')
            ->where('requester_type', OAuthAccount::class)
            ->update(['oauth_account_id' => DB::raw('requester_id')]);

        Schema::table('song_requests', function (Blueprint $table) {
            $table->dropMorphs('requester');
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->foreignId('oauth_id')->nullable()->constrained('oauth_accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        });

        DB::table('poll_votes')
            ->where('voter_type', User::class)
            ->update(['user_id' => DB::raw('voter_id')]);

        DB::table('poll_votes')
            ->where('voter_type', OAuthAccount::class)
            ->update(['oauth_id' => DB::raw('voter_id')]);

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique(['poll_id', 'voter_type', 'voter_id']);
            $table->dropMorphs('voter');
            $table->dropIndex('poll_votes_poll_id_index');
            $table->unique(['poll_id', 'oauth_id']);
            $table->unique(['poll_id', 'user_id']);
        });
    }
};
