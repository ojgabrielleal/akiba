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

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'endpoint']);
            $table->unique('endpoint');
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropUnique(['endpoint']);
            $table->unique(['user_id', 'endpoint']);
        });
    }
};
