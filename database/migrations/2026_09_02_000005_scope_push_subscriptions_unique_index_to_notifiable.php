<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::table('push_subscriptions')
                ->whereNotIn('id', fn ($query) => $query
                    ->selectRaw('MAX(id)')
                    ->from('push_subscriptions')
                    ->groupBy(['notifiable_type', 'notifiable_id', 'endpoint']))
                ->delete();
        } else {
            DB::statement(<<<'SQL'
                DELETE current_subscription
                FROM push_subscriptions current_subscription
                INNER JOIN push_subscriptions kept_subscription
                    ON current_subscription.endpoint = kept_subscription.endpoint
                    AND (
                        current_subscription.notifiable_type <=> kept_subscription.notifiable_type
                        AND current_subscription.notifiable_id <=> kept_subscription.notifiable_id
                    )
                    AND current_subscription.id < kept_subscription.id
            SQL);
        }

        Schema::table('push_subscriptions', function (Blueprint $table): void {
            if ($this->indexExists('push_subscriptions', 'push_subscriptions_endpoint_unique')) {
                $table->dropUnique('push_subscriptions_endpoint_unique');
            }

            if (! $this->indexExists('push_subscriptions', 'push_subscriptions_notifiable_endpoint_unique')) {
                $table->unique(
                    ['notifiable_type', 'notifiable_id', 'endpoint'],
                    'push_subscriptions_notifiable_endpoint_unique',
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table): void {
            if ($this->indexExists('push_subscriptions', 'push_subscriptions_notifiable_endpoint_unique')) {
                $table->dropUnique('push_subscriptions_notifiable_endpoint_unique');
            }

            if (! $this->indexExists('push_subscriptions', 'push_subscriptions_endpoint_unique')) {
                $table->unique('endpoint');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $definition): bool => $definition['name'] === $index);
    }
};
