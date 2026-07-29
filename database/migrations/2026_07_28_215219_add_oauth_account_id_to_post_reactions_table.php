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
        Schema::table('post_reactions', function (Blueprint $table) {
            $table->foreignId('oauth_account_id')
                ->nullable()
                ->after('post_id')
                ->constrained('oauth_accounts')
                ->cascadeOnDelete();

            $table->unique(['post_id', 'oauth_account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_reactions', function (Blueprint $table) {
            $table->dropUnique(['post_id', 'oauth_account_id']);
            $table->dropConstrainedForeignId('oauth_account_id');
        });
    }
};
