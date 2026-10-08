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
        if (! Schema::hasTable('oauth_accounts') || Schema::hasColumn('oauth_accounts', 'top_anime')) {
            return;
        }

        Schema::table('oauth_accounts', function (Blueprint $table) {
            $table->json('top_anime')->nullable()->after('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('oauth_accounts') || ! Schema::hasColumn('oauth_accounts', 'top_anime')) {
            return;
        }

        Schema::table('oauth_accounts', function (Blueprint $table) {
            $table->dropColumn('top_anime');
        });
    }
};
