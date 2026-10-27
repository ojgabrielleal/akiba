<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
            DELETE duplicate
            FROM page_views AS duplicate
            INNER JOIN page_views AS previous
                ON previous.id < duplicate.id
                AND previous.created_at >= DATE_SUB(duplicate.created_at, INTERVAL 59 SECOND)
                AND previous.created_at <= duplicate.created_at
                AND previous.actor_type <=> duplicate.actor_type
                AND previous.actor_id <=> duplicate.actor_id
                AND previous.ip_address <=> duplicate.ip_address
                AND previous.viewable_type <=> duplicate.viewable_type
                AND previous.viewable_id <=> duplicate.viewable_id
                AND previous.page_name <=> duplicate.page_name
                AND previous.page_url <=> duplicate.page_url
        SQL);
    }

    public function down(): void
    {
        // Registros inflados removidos não podem ser reconstruídos com segurança.
    }
};
