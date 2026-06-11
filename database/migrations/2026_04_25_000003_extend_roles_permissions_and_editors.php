<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('novel_editors')) {
            Schema::table('novel_editors', function (Blueprint $t) {
                if (!Schema::hasColumn('novel_editors', 'can_edit_chapters')) {
                    $t->boolean('can_edit_chapters')->default(true)->after('can_read_paid');
                }
                if (!Schema::hasColumn('novel_editors', 'can_publish_chapters')) {
                    $t->boolean('can_publish_chapters')->default(false)->after('can_edit_chapters');
                }
                if (!Schema::hasColumn('novel_editors', 'can_set_paid')) {
                    $t->boolean('can_set_paid')->default(false)->after('can_publish_chapters');
                }
                if (!Schema::hasColumn('novel_editors', 'can_edit_settings')) {
                    $t->boolean('can_edit_settings')->default(false)->after('can_set_paid');
                }
                if (!Schema::hasColumn('novel_editors', 'can_manage_team')) {
                    $t->boolean('can_manage_team')->default(false)->after('can_edit_settings');
                }
                if (!Schema::hasColumn('novel_editors', 'can_moderate_comments')) {
                    $t->boolean('can_moderate_comments')->default(false)->after('can_manage_team');
                }
                if (!Schema::hasColumn('novel_editors', 'role_label')) {
                    $t->string('role_label', 50)->nullable()->after('can_moderate_comments');
                }
            });
        }

        $this->seedPermissions();
        $this->seedRoles();
    }

    public function down(): void {
        if (Schema::hasTable('novel_editors')) {
            Schema::table('novel_editors', function (Blueprint $t) {
                foreach (['can_edit_chapters', 'can_publish_chapters', 'can_set_paid',
                          'can_edit_settings', 'can_manage_team', 'can_moderate_comments', 'role_label'] as $col) {
                    if (Schema::hasColumn('novel_editors', $col)) {
                        $t->dropColumn($col);
                    }
                }
            });
        }
    }

    private function seedPermissions(): void {
        $permissions = [
            'novels.create',
            'novels.edit_own',
            'novels.edit_any',
            'novels.delete_own',
            'novels.delete_any',
            'novels.publish_own',
            'novels.unpublish_any',
            'novels.feature',
            'novels.set_restricted',
            'novels.set_pricing',
            'chapters.create_own',
            'chapters.create_any',
            'chapters.edit_own',
            'chapters.edit_any',
            'chapters.delete_own',
            'chapters.delete_any',
            'chapters.publish',
            'chapters.set_paid',
            'chapters.read_paid_free',
            'comments.create',
            'comments.edit_own',
            'comments.delete_own',
            'comments.delete_any',
            'comments.moderate',
            'comments.ban_user',
            'users.view_admin',
            'users.edit_any',
            'users.ban',
            'users.delete',
            'users.assign_roles',
            'users.impersonate',
            'finances.view_admin',
            'finances.approve_payments',
            'finances.refund',
            'finances.adjust_balance',
            'finances.approve_withdrawals',
            'admin.access_panel',
            'admin.modify_settings',
            'admin.manage_homepage',
            'admin.manage_pages',
            'admin.manage_promo',
            'admin.manage_stickers',
            'admin.view_logs',
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function seedRoles(): void {
        try { app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions(); } catch (\Throwable $e) {}

        $rolesMap = [
            'owner' => 'all',

            'super_admin' => 'all_except_dangerous',

            'deputy_admin' => [
                'admin.access_panel', 'admin.modify_settings', 'admin.manage_homepage',
                'admin.manage_pages', 'admin.manage_promo', 'admin.view_logs',
                'novels.edit_any', 'novels.unpublish_any', 'novels.feature', 'novels.set_restricted',
                'chapters.edit_any', 'chapters.delete_any', 'chapters.read_paid_free',
                'comments.delete_any', 'comments.moderate', 'comments.ban_user',
                'users.view_admin', 'users.edit_any', 'users.ban', 'users.assign_roles',
                'finances.view_admin', 'finances.approve_payments', 'finances.approve_withdrawals',
            ],

            'moderator' => [
                'admin.access_panel',
                'novels.unpublish_any', 'novels.set_restricted',
                'chapters.edit_any', 'chapters.delete_any', 'chapters.read_paid_free',
                'comments.delete_any', 'comments.moderate', 'comments.ban_user',
                'users.view_admin', 'users.ban',
            ],

            'editor' => [
                'novels.edit_any',
                'chapters.edit_any',
                'chapters.read_paid_free',
                'admin.access_panel',
            ],

            'translator' => [
                'chapters.edit_any',
                'chapters.read_paid_free',
            ],

            'beta_tester' => [
                'chapters.read_paid_free',
            ],

            'author' => [
                'novels.create', 'novels.edit_own', 'novels.delete_own', 'novels.publish_own',
                'novels.set_pricing',
                'chapters.create_own', 'chapters.edit_own', 'chapters.delete_own',
                'chapters.publish', 'chapters.set_paid',
                'comments.create', 'comments.edit_own', 'comments.delete_own',
            ],

            'reader' => [
                'comments.create', 'comments.edit_own', 'comments.delete_own',
            ],
        ];

        $allPerms = DB::table('permissions')->where('guard_name', 'web')->pluck('id', 'name');

        $dangerous = ['users.delete', 'users.impersonate'];

        foreach ($rolesMap as $roleName => $permsConfig) {
            $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first();
            if (!$role) {
                $roleId = DB::table('roles')->insertGetId([
                    'name' => $roleName, 'guard_name' => 'web',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            } else {
                $roleId = $role->id;
            }

            if ($permsConfig === 'all') {
                $permIds = $allPerms->values()->all();
            } elseif ($permsConfig === 'all_except_dangerous') {
                $permIds = $allPerms->except($dangerous)->values()->all();
            } else {
                $permIds = collect($permsConfig)->map(fn($n) => $allPerms[$n] ?? null)->filter()->values()->all();
            }

            DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
            foreach ($permIds as $pid) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $pid,
                    'role_id' => $roleId,
                ]);
            }
        }

        try { app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions(); } catch (\Throwable $e) {}
    }
};
