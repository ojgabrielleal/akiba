<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('oauth_accounts', 'account_token_hash')) {
            return;
        }

        Schema::table('oauth_accounts', function (Blueprint $table) {
            if ($this->indexExists('oauth_accounts', 'oauth_accounts_account_token_hash_unique')) {
                $table->dropUnique('oauth_accounts_account_token_hash_unique');
            }

            $table->dropColumn('account_token_hash');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('oauth_accounts', 'account_token_hash')) {
            return;
        }

        Schema::table('oauth_accounts', function (Blueprint $table) {
            $table->string('account_token_hash')->unique()->after('provider');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
