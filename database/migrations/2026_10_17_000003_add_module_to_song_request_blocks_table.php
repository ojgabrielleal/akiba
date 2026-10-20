<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('song_request_blocks', function (Blueprint $table) {
            $table->dropUnique(['requester_type', 'requester_id']);
            $table->string('module', 40)->default('song_requests')->after('requester_id');
            $table->unique(['requester_type', 'requester_id', 'module'], 'song_request_blocks_identity_module_unique');
        });
    }

    public function down(): void
    {
        Schema::table('song_request_blocks', function (Blueprint $table) {
            $table->dropUnique('song_request_blocks_identity_module_unique');
            $table->dropColumn('module');
            $table->unique(['requester_type', 'requester_id']);
        });
    }
};
