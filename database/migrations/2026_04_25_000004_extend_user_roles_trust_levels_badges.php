<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $t) {
                if (!Schema::hasColumn('users', 'trust_level')) {
                    $t->tinyInteger('trust_level')->default(0)->after('email');
                }
                if (!Schema::hasColumn('users', 'is_verified')) {
                    $t->boolean('is_verified')->default(false)->after('trust_level');
                }
                if (!Schema::hasColumn('users', 'verified_at')) {
                    $t->timestamp('verified_at')->nullable()->after('is_verified');
                }
                if (!Schema::hasColumn('users', 'is_premium')) {
                    $t->boolean('is_premium')->default(false)->after('verified_at');
                }
                if (!Schema::hasColumn('users', 'premium_until')) {
                    $t->timestamp('premium_until')->nullable()->after('is_premium');
                }
                if (!Schema::hasColumn('users', 'patron_tier')) {
                    $t->tinyInteger('patron_tier')->default(0)->after('premium_until');
                }
                if (!Schema::hasColumn('users', 'reviewer_badge')) {
                    $t->boolean('reviewer_badge')->default(false)->after('patron_tier');
                }
                if (!Schema::hasColumn('users', 'profile_title')) {
                    $t->string('profile_title', 60)->nullable()->after('reviewer_badge');
                }
            });
            try {
                Schema::table('users', function (Blueprint $t) {
                    $t->index(['trust_level', 'is_verified'], 'users_trust_verified_idx');
                });
            } catch (\Throwable $e) {  }
        }

        if (Schema::hasTable('chapters')) {
            Schema::table('chapters', function (Blueprint $t) {
                if (!Schema::hasColumn('chapters', 'is_patron_advance')) {
                    $t->boolean('is_patron_advance')->default(false)->after('is_locked');
                }
                if (!Schema::hasColumn('chapters', 'patron_advance_days')) {
                    $t->smallInteger('patron_advance_days')->default(0)->after('is_patron_advance');
                }
            });
        }

        $this->seedPermissions();

        $this->seedRoles();
    }

    public function down(): void {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $t) {
                foreach (['trust_level', 'is_verified', 'verified_at', 'is_premium',
                          'premium_until', 'patron_tier', 'reviewer_badge', 'profile_title'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $t->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('chapters')) {
            Schema::table('chapters', function (Blueprint $t) {
                foreach (['is_patron_advance', 'patron_advance_days'] as $col) {
                    if (Schema::hasColumn('chapters', $col)) {
                        $t->dropColumn($col);
                    }
                }
            });
        }
    }

    private function seedPermissions(): void {
        $newPermissions = [
            'chapters.read_advance',
            'chapters.read_premium_only',
            'reviews.create',
            'reviews.edit_own',
            'reviews.delete_own',
            'reviews.delete_any',
            'reviews.feature',
            'reviews.weighted_vote',
            'collections.create',
            'collections.edit_own',
            'collections.edit_any',
            'collections.feature',
            'collections.delete_own',
            'collections.delete_any',
            'groups.create',
            'groups.edit_own',
            'groups.invite_members',
            'groups.delete_own',
            'groups.assign_novels',
            'users.follow',
            'users.message_send',
            'users.message_receive',
            'users.profile_customize',
            'chapters.bulk_upload',
            'chapters.schedule',
            'chapters.import_from_url',
            'novels.transfer_ownership',
            'novels.translate_existing',
            'donations.send',
            'donations.refund_own',
            'patrons.early_access',
            'comments.bypass_slowmode',
            'comments.flag',
            'novels.suggest_edit',
        ];
        foreach ($newPermissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function seedRoles(): void {
        try { app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions(); } catch (\Throwable $e) {}

        $newRoles = [
            'premium_reader' => [
                'chapters.read_paid_free',
                'chapters.read_advance',
                'chapters.read_premium_only',
                'patrons.early_access',
                'comments.bypass_slowmode',
            ],

            'patron' => [
                'chapters.read_advance',
                'patrons.early_access',
                'comments.bypass_slowmode',
                'donations.send', 'donations.refund_own',
            ],

            'verified_author' => [
                'novels.create', 'novels.edit_own', 'novels.delete_own', 'novels.publish_own',
                'novels.set_pricing', 'novels.transfer_ownership',
                'chapters.create_own', 'chapters.edit_own', 'chapters.delete_own',
                'chapters.publish', 'chapters.set_paid', 'chapters.bulk_upload',
                'chapters.schedule', 'chapters.import_from_url',
                'comments.create', 'comments.edit_own', 'comments.delete_own',
                'donations.send',
                'collections.create', 'collections.edit_own',
                'reviews.create', 'reviews.edit_own',
                'comments.bypass_slowmode',
            ],

            'curator' => [
                'collections.create', 'collections.edit_own', 'collections.edit_any',
                'collections.feature', 'collections.delete_own',
                'reviews.feature',
                'comments.bypass_slowmode',
                'novels.suggest_edit',
            ],

            'group_leader' => [
                'groups.create', 'groups.edit_own', 'groups.invite_members',
                'groups.delete_own', 'groups.assign_novels',
                'novels.translate_existing',
                'novels.create', 'novels.edit_own', 'novels.publish_own',
                'chapters.create_own', 'chapters.edit_own', 'chapters.publish',
                'chapters.bulk_upload', 'chapters.schedule',
                'comments.create', 'comments.edit_own',
            ],

            'trusted_user' => [
                'comments.bypass_slowmode',
                'comments.flag',
                'novels.suggest_edit',
                'reviews.create', 'reviews.edit_own',
                'collections.create', 'collections.edit_own',
            ],

            'reviewer' => [
                'reviews.create', 'reviews.edit_own', 'reviews.weighted_vote',
                'comments.bypass_slowmode',
                'novels.suggest_edit',
            ],

            'donor' => [
                'donations.send',
                'comments.bypass_slowmode',
            ],
        ];

        $allPerms = DB::table('permissions')->where('guard_name', 'web')->pluck('id', 'name');

        foreach ($newRoles as $roleName => $permsConfig) {
            $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first();
            if (!$role) {
                $roleId = DB::table('roles')->insertGetId([
                    'name' => $roleName, 'guard_name' => 'web',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            } else {
                $roleId = $role->id;
            }

            $permIds = collect($permsConfig)->map(fn($n) => $allPerms[$n] ?? null)->filter()->values()->all();

            foreach ($permIds as $pid) {
                DB::table('role_has_permissions')->updateOrInsert(
                    ['permission_id' => $pid, 'role_id' => $roleId],
                    []
                );
            }
        }

        foreach (['owner', 'super_admin'] as $bigRole) {
            $r = DB::table('roles')->where('name', $bigRole)->first();
            if (!$r) continue;
            $existing = DB::table('role_has_permissions')->where('role_id', $r->id)->pluck('permission_id');
            $missing = $allPerms->values()->diff($existing);
            foreach ($missing as $pid) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $pid, 'role_id' => $r->id,
                ]);
            }
        }
        $author = DB::table('roles')->where('name', 'author')->first();
        if ($author) {
            foreach (['donations.send', 'reviews.create', 'reviews.edit_own',
                      'collections.create', 'collections.edit_own',
                      'chapters.bulk_upload', 'chapters.schedule',
                      'users.follow', 'users.message_send', 'users.message_receive',
                      'users.profile_customize'] as $p) {
                if (isset($allPerms[$p])) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $allPerms[$p], 'role_id' => $author->id,
                    ]);
                }
            }
        }
        $reader = DB::table('roles')->where('name', 'reader')->first();
        if ($reader) {
            foreach (['donations.send', 'reviews.create', 'reviews.edit_own',
                      'users.follow', 'users.message_send', 'users.message_receive'] as $p) {
                if (isset($allPerms[$p])) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $allPerms[$p], 'role_id' => $reader->id,
                    ]);
                }
            }
        }

        try { app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions(); } catch (\Throwable $e) {}
    }
};
