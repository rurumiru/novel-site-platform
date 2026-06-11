<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('translation_teams')) {
            Schema::create('translation_teams', function (Blueprint $t) {
                $t->id();
                $t->string('name', 120);
                $t->string('slug', 140)->unique();
                $t->foreignId('leader_id')->constrained('users')->cascadeOnDelete();
                $t->text('description')->nullable();
                $t->text('mission')->nullable();
                $t->string('logo_path', 255)->nullable();
                $t->string('banner_path', 255)->nullable();
                $t->string('website', 255)->nullable();
                $t->string('discord', 255)->nullable();
                $t->string('telegram', 255)->nullable();
                $t->string('vk', 255)->nullable();
                $t->enum('status', ['active', 'recruiting', 'closed', 'on_hiatus', 'disbanded'])
                  ->default('recruiting');
                $t->boolean('is_official')->default(false);
                $t->boolean('is_featured')->default(false);
                $t->unsignedInteger('balance')->default(0);
                $t->unsignedInteger('total_earned')->default(0);
                $t->json('settings')->nullable();
                $t->timestamps();

                $t->index(['status', 'is_featured']);
                $t->index('leader_id');
            });
        }

        if (!Schema::hasTable('translation_team_members')) {
            Schema::create('translation_team_members', function (Blueprint $t) {
                $t->id();
                $t->foreignId('team_id')->constrained('translation_teams')->cascadeOnDelete();
                $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $t->enum('role', [
                    'leader', 'coordinator', 'translator', 'editor',
                    'typesetter', 'illustrator', 'tlc', 'beta', 'member',
                ])->default('member');
                $t->string('title', 80)->nullable();
                $t->decimal('revenue_share', 5, 2)->default(0);
                $t->boolean('is_active')->default(true);
                $t->boolean('can_assign_novels')->default(false);
                $t->boolean('can_invite_members')->default(false);
                $t->boolean('can_manage_payouts')->default(false);
                $t->timestamp('joined_at')->useCurrent();
                $t->timestamp('left_at')->nullable();
                $t->timestamps();

                $t->unique(['team_id', 'user_id']);
                $t->index(['team_id', 'is_active']);
            });
        }

        if (!Schema::hasTable('novel_team')) {
            Schema::create('novel_team', function (Blueprint $t) {
                $t->id();
                $t->foreignId('novel_id')->constrained('novels')->cascadeOnDelete();
                $t->foreignId('team_id')->constrained('translation_teams')->cascadeOnDelete();
                $t->decimal('team_revenue_share', 5, 2)->default(0);
                $t->boolean('is_primary')->default(true);
                $t->boolean('show_credits')->default(true);
                $t->timestamp('assigned_at')->useCurrent();
                $t->timestamps();

                $t->unique(['novel_id', 'team_id']);
                $t->index(['team_id', 'is_primary']);
            });
        }

        if (!Schema::hasTable('team_invites')) {
            Schema::create('team_invites', function (Blueprint $t) {
                $t->id();
                $t->foreignId('team_id')->constrained('translation_teams')->cascadeOnDelete();
                $t->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
                $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->string('email', 255)->nullable();
                $t->string('token', 64)->unique();
                $t->enum('proposed_role', [
                    'coordinator', 'translator', 'editor',
                    'typesetter', 'illustrator', 'tlc', 'beta', 'member',
                ])->default('member');
                $t->decimal('proposed_share', 5, 2)->default(0);
                $t->enum('status', ['pending', 'accepted', 'rejected', 'expired', 'revoked'])->default('pending');
                $t->timestamp('expires_at')->nullable();
                $t->timestamp('responded_at')->nullable();
                $t->timestamps();

                $t->index(['team_id', 'status']);
                $t->index('user_id');
            });
        }

        if (!Schema::hasTable('team_payouts')) {
            Schema::create('team_payouts', function (Blueprint $t) {
                $t->id();
                $t->foreignId('team_id')->constrained('translation_teams')->cascadeOnDelete();
                $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $t->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
                $t->unsignedInteger('amount');
                $t->string('comment', 255)->nullable();
                $t->enum('source', ['novel_revenue', 'donation', 'manual_grant'])->default('novel_revenue');
                $t->foreignId('novel_id')->nullable()->constrained('novels')->nullOnDelete();
                $t->timestamp('paid_at')->useCurrent();
                $t->timestamps();

                $t->index(['team_id', 'paid_at']);
                $t->index(['user_id', 'paid_at']);
            });
        }

        $perms = [
            'teams.create',
            'teams.edit_own',
            'teams.edit_any',
            'teams.delete_own',
            'teams.disband_own',
            'teams.set_official',
            'teams.set_featured',
            'teams.invite_members',
            'teams.kick_members',
            'teams.set_member_role',
            'teams.set_member_share',
            'teams.assign_novel',
            'teams.unassign_novel',
            'teams.set_revenue_share',
            'teams.process_payout',
            'teams.view_finance',
            'teams.export_payouts',
        ];
        foreach ($perms as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm, 'guard_name' => 'web'],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        try { app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions(); } catch (\Throwable $e) {}
        $allPerms = DB::table('permissions')->where('guard_name', 'web')->pluck('id', 'name');

        $roleMap = [
            'owner'         => array_keys($allPerms->toArray()),
            'super_admin'   => array_keys($allPerms->toArray()),
            'group_leader'  => [
                'teams.create','teams.edit_own','teams.delete_own','teams.disband_own',
                'teams.invite_members','teams.kick_members','teams.set_member_role','teams.set_member_share',
                'teams.assign_novel','teams.unassign_novel','teams.set_revenue_share',
                'teams.process_payout','teams.view_finance','teams.export_payouts',
            ],
            'guild_master'  => [
                'teams.create','teams.edit_own','teams.delete_own','teams.disband_own',
                'teams.invite_members','teams.kick_members','teams.set_member_role','teams.set_member_share',
                'teams.assign_novel','teams.unassign_novel','teams.set_revenue_share',
                'teams.process_payout','teams.view_finance','teams.export_payouts',
            ],
            'editor_in_chief' => ['teams.edit_any','teams.set_official','teams.set_featured'],
            'translator'    => ['teams.create','teams.edit_own','teams.invite_members'],
            'author'        => ['teams.create','teams.edit_own','teams.invite_members','teams.assign_novel'],
        ];
        foreach ($roleMap as $roleName => $permList) {
            $r = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first();
            if (!$r) continue;
            foreach ($permList as $p) {
                if (!isset($allPerms[$p])) continue;
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $allPerms[$p], 'role_id' => $r->id,
                ]);
            }
        }
        try { app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions(); } catch (\Throwable $e) {}
    }

    public function down(): void {
        Schema::dropIfExists('team_payouts');
        Schema::dropIfExists('team_invites');
        Schema::dropIfExists('novel_team');
        Schema::dropIfExists('translation_team_members');
        Schema::dropIfExists('translation_teams');
    }
};
