<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $oldId = DB::table('permissions')->where('name', 'song.request.toggle')->value('id');
            $roles = DB::table('permissions_pivot')->where('permission_id', $oldId)->pluck('role_id');
            foreach (['block' => 'bloquear', 'unblock' => 'desbloquear'] as $action => $label) {
                $name = "song.request.{$action}";
                $id = DB::table('permissions')->where('name', $name)->value('id');
                if (! $id) {
                    $id = DB::table('permissions')->insertGetId([
                        'uuid' => (string) Str::uuid(), 'name' => $name,
                        'label' => "[Pedidos Música] Pode {$label} ouvinte",
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                foreach ($roles as $roleId) {
                    DB::table('permissions_pivot')->updateOrInsert(['permission_id' => $id, 'role_id' => $roleId]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $ids = DB::table('permissions')->whereIn('name', ['song.request.block', 'song.request.unblock'])->pluck('id');
            DB::table('permissions_pivot')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
        });
    }
};
