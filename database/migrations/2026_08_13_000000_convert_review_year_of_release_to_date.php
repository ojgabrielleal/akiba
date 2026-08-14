<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
            UPDATE posts
            SET metadata = JSON_SET(
                JSON_REMOVE(metadata, '$.year_of_release'),
                '$.date_of_release',
                CASE
                    WHEN JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.year_of_release')) REGEXP '^[0-9]{4}$'
                        THEN CONCAT(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.year_of_release')), '-01-01')
                    ELSE JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.year_of_release'))
                END
            )
            WHERE module = 'review'
                AND JSON_EXTRACT(metadata, '$.year_of_release') IS NOT NULL
                AND JSON_EXTRACT(metadata, '$.date_of_release') IS NULL
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
            UPDATE posts
            SET metadata = JSON_SET(
                JSON_REMOVE(metadata, '$.date_of_release'),
                '$.year_of_release',
                LEFT(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.date_of_release')), 4)
            )
            WHERE module = 'review'
                AND JSON_EXTRACT(metadata, '$.date_of_release') IS NOT NULL
        SQL);
    }
};
