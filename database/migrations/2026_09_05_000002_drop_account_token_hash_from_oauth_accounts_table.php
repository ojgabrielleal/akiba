<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('oauth_accounts', 'account_token_hash')) {
            return;
        }

        $indexes = $this->indexesForColumn('oauth_accounts', 'account_token_hash');

        Schema::table('oauth_accounts', function (Blueprint $table) use ($indexes) {
            foreach ($indexes as $index) {
                $index['unique']
                    ? $table->dropUnique($index['name'])
                    : $table->dropIndex($index['name']);
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

    private function indexesForColumn(string $table, string $column): array
    {
        return collect(Schema::getIndexes($table))
            ->filter(fn (array $definition): bool => in_array($column, $definition['columns'], true))
            ->values()
            ->all();
    }
};
