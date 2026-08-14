<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            DELETE current_subscription
            FROM push_subscriptions current_subscription
            INNER JOIN push_subscriptions kept_subscription
                ON current_subscription.endpoint = kept_subscription.endpoint
                AND (
                    (
                        current_subscription.user_id IS NULL
                        AND kept_subscription.user_id IS NOT NULL
                    )
                    OR (
                        (
                            current_subscription.user_id IS NULL
                            AND kept_subscription.user_id IS NULL
                        )
                        OR (
                            current_subscription.user_id IS NOT NULL
                            AND kept_subscription.user_id IS NOT NULL
                        )
                    )
                    AND current_subscription.id < kept_subscription.id
                )
        SQL);

        $this->createIndexIfMissing('push_subscriptions', 'push_subscriptions_user_id_index', ['user_id']);

        Schema::table('push_subscriptions', function (Blueprint $table) {
            if ($this->indexExists('push_subscriptions', 'push_subscriptions_user_id_endpoint_unique')) {
                $table->dropUnique('push_subscriptions_user_id_endpoint_unique');
            }

            if (! $this->indexExists('push_subscriptions', 'push_subscriptions_endpoint_unique')) {
                $table->unique('endpoint');
            }
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            if ($this->indexExists('push_subscriptions', 'push_subscriptions_endpoint_unique')) {
                $table->dropUnique('push_subscriptions_endpoint_unique');
            }

            if (! $this->indexExists('push_subscriptions', 'push_subscriptions_user_id_endpoint_unique')) {
                $table->unique(['user_id', 'endpoint']);
            }
        });
    }

    private function createIndexIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($index, $columns) {
            $table->index($columns, $index);
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }
};
