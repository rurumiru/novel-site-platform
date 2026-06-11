<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $this->seedPermissions();
        $this->seedRoles();
    }

    public function down(): void {
    }

    private function seedPermissions(): void {
        $perms = [
            'admin.access_panel', 'admin.view_dashboard', 'admin.view_logs',
            'admin.manage_settings', 'admin.manage_appearance', 'admin.manage_navigation',
            'admin.maintenance_mode', 'admin.cache_clear',

            'moderation.view_queue', 'moderation.handle_report', 'moderation.dismiss_report',
            'moderation.escalate_report', 'moderation.assign_to_self',
            'moderation.lock_thread', 'moderation.pin_thread', 'moderation.move_thread',
            'moderation.delete_any_comment', 'moderation.edit_any_comment',
            'moderation.hide_any_chapter', 'moderation.takedown_novel',
            'moderation.review_appeals', 'moderation.view_audit',

            'users.warn', 'users.ban_temp', 'users.ban_perm',
            'users.shadow_ban', 'users.ip_block', 'users.unban',
            'users.impersonate', 'users.merge', 'users.delete',
            'users.assign_roles', 'users.assign_badges', 'users.verify',
            'users.view_private', 'users.view_email', 'users.view_ip',
            'users.export_data', 'users.reset_password',

            'finance.view_transactions', 'finance.view_balance_any',
            'finance.process_payout', 'finance.refund_purchase', 'finance.refund_donation',
            'finance.adjust_balance', 'finance.view_reports', 'finance.export_reports',
            'finance.manage_payment_methods', 'finance.manage_pricing',

            'analytics.view_global', 'analytics.view_user_metrics',
            'analytics.view_novel_metrics', 'analytics.export_data',
            'analytics.view_ab_tests', 'analytics.create_ab_test',

            'blog.create_post', 'blog.edit_own_post', 'blog.edit_any_post',
            'blog.delete_own_post', 'blog.delete_any_post',
            'blog.publish', 'blog.feature_post',
            'news.create', 'news.edit', 'news.delete', 'news.publish_global',

            'badges.create', 'badges.edit', 'badges.delete',
            'badges.assign_to_user', 'badges.revoke_from_user',

            'taxonomy.create_genre', 'taxonomy.edit_genre', 'taxonomy.delete_genre',
            'taxonomy.create_tag', 'taxonomy.edit_tag', 'taxonomy.delete_tag',
            'taxonomy.merge_tags',

            'promo.feature_homepage', 'promo.feature_catalog_top',
            'promo.set_banner', 'promo.boost_novel',
            'promo.run_event', 'promo.create_collection_official',

            'api.access', 'api.create_token', 'api.revoke_token', 'api.view_quotas',

            'support.view_tickets', 'support.reply_tickets', 'support.close_tickets',
            'support.escalate_tickets', 'support.merge_tickets',

            'guilds.manage_any', 'guilds.feature', 'guilds.set_official',

            'features.access_beta', 'features.toggle_flag', 'features.report_bug_priority',

            'illustrations.upload_any_novel', 'illustrations.set_official_cover',

            'content.tag_18plus', 'content.review_18plus_appeal',
            'content.lock_novel', 'content.unlock_novel',

            'authors.set_donation_goal', 'authors.create_subscriber_only_post',
            'authors.upload_audio_chapter', 'authors.create_serial_event',
        ];

        foreach ($perms as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function seedRoles(): void {
        try { app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions(); } catch (\Throwable $e) {}

        $allPerms = DB::table('permissions')->where('guard_name', 'web')->pluck('id', 'name');

        $newRoles = [
            'editor_in_chief' => [
                'admin.access_panel', 'admin.view_dashboard',
                'moderation.view_queue', 'moderation.handle_report', 'moderation.lock_thread', 'moderation.pin_thread',
                'moderation.delete_any_comment', 'moderation.edit_any_comment', 'moderation.hide_any_chapter',
                'blog.create_post', 'blog.edit_any_post', 'blog.delete_any_post', 'blog.publish', 'blog.feature_post',
                'news.create', 'news.edit', 'news.publish_global',
                'promo.feature_homepage', 'promo.feature_catalog_top', 'promo.set_banner',
                'promo.create_collection_official', 'promo.boost_novel', 'promo.run_event',
                'taxonomy.create_genre', 'taxonomy.edit_genre',
                'taxonomy.create_tag', 'taxonomy.edit_tag', 'taxonomy.merge_tags',
                'reviews.feature', 'collections.feature', 'collections.edit_any',
            ],

            'senior_moderator' => [
                'admin.access_panel', 'admin.view_dashboard',
                'moderation.view_queue', 'moderation.handle_report', 'moderation.dismiss_report',
                'moderation.escalate_report', 'moderation.lock_thread', 'moderation.pin_thread',
                'moderation.move_thread', 'moderation.delete_any_comment', 'moderation.edit_any_comment',
                'moderation.hide_any_chapter', 'moderation.takedown_novel',
                'moderation.review_appeals', 'moderation.view_audit',
                'users.warn', 'users.ban_temp', 'users.ban_perm', 'users.shadow_ban',
                'users.unban', 'users.assign_badges', 'users.view_private', 'users.view_ip',
                'content.lock_novel', 'content.unlock_novel', 'content.tag_18plus',
            ],

            'junior_moderator' => [
                'admin.access_panel',
                'moderation.view_queue', 'moderation.handle_report', 'moderation.dismiss_report',
                'moderation.lock_thread', 'moderation.pin_thread',
                'moderation.delete_any_comment', 'moderation.edit_any_comment',
                'users.warn',
            ],

            'community_manager' => [
                'admin.access_panel',
                'moderation.view_queue', 'moderation.handle_report',
                'moderation.lock_thread', 'moderation.pin_thread',
                'reviews.feature', 'reviews.delete_any',
                'collections.feature', 'collections.edit_any',
                'blog.create_post', 'blog.edit_any_post', 'blog.publish', 'blog.feature_post',
                'news.create', 'news.publish_global',
                'badges.assign_to_user', 'badges.revoke_from_user',
            ],

            'support_agent' => [
                'admin.access_panel',
                'support.view_tickets', 'support.reply_tickets', 'support.close_tickets',
                'support.merge_tickets',
                'users.view_private', 'users.view_email', 'users.reset_password', 'users.unban',
                'finance.view_transactions',
            ],
            'support_lead' => [
                'admin.access_panel',
                'support.view_tickets', 'support.reply_tickets', 'support.close_tickets',
                'support.escalate_tickets', 'support.merge_tickets',
                'users.view_private', 'users.view_email', 'users.view_ip', 'users.reset_password',
                'users.warn', 'users.ban_temp', 'users.unban', 'users.merge',
                'finance.view_transactions', 'finance.refund_purchase', 'finance.refund_donation',
            ],

            'finance_manager' => [
                'admin.access_panel', 'admin.view_dashboard',
                'finance.view_transactions', 'finance.view_balance_any',
                'finance.process_payout', 'finance.refund_purchase', 'finance.refund_donation',
                'finance.adjust_balance', 'finance.view_reports', 'finance.export_reports',
                'finance.manage_payment_methods', 'finance.manage_pricing',
                'analytics.view_global', 'analytics.export_data',
            ],

            'analyst' => [
                'admin.access_panel', 'admin.view_dashboard',
                'analytics.view_global', 'analytics.view_user_metrics',
                'analytics.view_novel_metrics', 'analytics.export_data',
                'analytics.view_ab_tests',
                'finance.view_reports',
            ],

            'content_writer' => [
                'blog.create_post', 'blog.edit_own_post', 'blog.delete_own_post', 'blog.publish',
                'news.create', 'news.edit',
                'comments.bypass_slowmode',
            ],

            'beta_tester' => [
                'features.access_beta', 'features.report_bug_priority',
                'comments.bypass_slowmode',
            ],

            'illustrator' => [
                'illustrations.upload_any_novel', 'illustrations.set_official_cover',
                'comments.bypass_slowmode',
            ],

            'translator' => [
                'novels.create', 'novels.edit_own', 'novels.publish_own',
                'novels.translate_existing',
                'chapters.create_own', 'chapters.edit_own', 'chapters.publish',
                'chapters.bulk_upload', 'chapters.schedule',
                'comments.create', 'comments.edit_own',
            ],

            'guild_master' => [
                'admin.access_panel',
                'guilds.manage_any', 'guilds.feature', 'guilds.set_official',
                'groups.create', 'groups.edit_own', 'groups.invite_members',
                'groups.delete_own', 'groups.assign_novels',
                'novels.translate_existing', 'novels.create', 'novels.edit_own',
                'chapters.create_own', 'chapters.edit_own', 'chapters.publish',
                'chapters.bulk_upload',
            ],

            'vip_silver' => [
                'chapters.read_advance',
                'comments.bypass_slowmode',
                'users.profile_customize',
            ],
            'vip_gold' => [
                'chapters.read_advance', 'chapters.read_premium_only', 'chapters.read_paid_free',
                'patrons.early_access',
                'comments.bypass_slowmode',
                'users.profile_customize',
                'features.access_beta',
            ],
            'vip_platinum' => [
                'chapters.read_advance', 'chapters.read_premium_only', 'chapters.read_paid_free',
                'patrons.early_access',
                'comments.bypass_slowmode',
                'users.profile_customize',
                'features.access_beta',
                'donations.send', 'reviews.weighted_vote',
            ],

            'donor_bronze' => ['donations.send', 'comments.bypass_slowmode'],
            'donor_silver' => ['donations.send', 'comments.bypass_slowmode', 'chapters.read_advance', 'patrons.early_access'],
            'donor_gold'   => [
                'donations.send', 'comments.bypass_slowmode',
                'chapters.read_advance', 'patrons.early_access',
                'chapters.read_premium_only', 'features.access_beta',
                'users.profile_customize',
            ],

            'founder' => [
                'admin.access_panel', 'admin.view_dashboard', 'admin.view_logs',
                'features.access_beta',
                'users.profile_customize', 'reviews.feature', 'reviews.weighted_vote',
                'comments.bypass_slowmode',
                'collections.create', 'collections.edit_any', 'collections.feature',
                'badges.assign_to_user',
            ],
            'ambassador' => [
                'reviews.create', 'reviews.weighted_vote', 'reviews.feature',
                'collections.create', 'collections.edit_own', 'collections.feature',
                'comments.bypass_slowmode',
                'users.profile_customize',
                'features.access_beta',
                'novels.suggest_edit',
            ],
        ];

        foreach ($newRoles as $roleName => $permsConfig) {
            $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first();
            $roleId = $role->id ?? DB::table('roles')->insertGetId([
                'name' => $roleName, 'guard_name' => 'web',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            foreach ($permsConfig as $permName) {
                if (!isset($allPerms[$permName])) continue;
                DB::table('role_has_permissions')->updateOrInsert(
                    ['permission_id' => $allPerms[$permName], 'role_id' => $roleId],
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

        $deputy = DB::table('roles')->where('name', 'deputy_admin')->first();
        if ($deputy) {
            $skipForDeputy = ['finance.adjust_balance', 'users.impersonate', 'users.delete', 'admin.maintenance_mode'];
            foreach ($allPerms as $name => $pid) {
                if (in_array($name, $skipForDeputy, true)) continue;
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $pid, 'role_id' => $deputy->id,
                ]);
            }
        }

        $mod = DB::table('roles')->where('name', 'moderator')->first();
        if ($mod) {
            foreach (['moderation.view_queue', 'moderation.handle_report', 'moderation.dismiss_report',
                      'moderation.lock_thread', 'moderation.pin_thread', 'moderation.delete_any_comment',
                      'moderation.edit_any_comment', 'users.warn', 'users.ban_temp',
                      'admin.access_panel'] as $p) {
                if (isset($allPerms[$p])) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $allPerms[$p], 'role_id' => $mod->id,
                    ]);
                }
            }
        }

        try { app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions(); } catch (\Throwable $e) {}
    }
};
