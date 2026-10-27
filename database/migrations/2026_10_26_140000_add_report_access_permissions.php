<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const PERMISSIONS = [
        'report.publication.view' => '[Relatórios] Visualizar acessos de publicações',
        'report.podcast.view' => '[Relatórios] Visualizar acessos de podcasts',
        'report.page.view' => '[Relatórios] Visualizar acessos de páginas',
    ];

    public function up(): void
    {
        $reportPermissionId = DB::table('permissions')
            ->where('name', 'report.module.view')
            ->value('id');
        $roleIds = $reportPermissionId
            ? DB::table('permissions_pivot')->where('permission_id', $reportPermissionId)->pluck('role_id')
            : collect();

        foreach (self::PERMISSIONS as $name => $label) {
            $permissionId = DB::table('permissions')->where('name', $name)->value('id');

            if (! $permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'name' => $name,
                    'label' => $label,
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
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_keys(self::PERMISSIONS))
            ->pluck('id');

        DB::table('permissions_pivot')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
