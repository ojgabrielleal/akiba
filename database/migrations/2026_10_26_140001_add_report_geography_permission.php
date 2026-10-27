<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const NAME = 'report.geography.view';

    public function up(): void
    {
        $reportPermissionId = DB::table('permissions')
            ->where('name', 'report.module.view')
            ->value('id');
        $roleIds = $reportPermissionId
            ? DB::table('permissions_pivot')->where('permission_id', $reportPermissionId)->pluck('role_id')
            : collect();
        $permissionId = DB::table('permissions')->where('name', self::NAME)->value('id');

        if (! $permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => self::NAME,
                'label' => '[Relatórios] Visualizar mapa e origem dos acessos',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($roleIds as $roleId) {
            DB::table('permissions_pivot')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('name', self::NAME)->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table('permissions_pivot')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
    }
};
