<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('translation_teams')) {
            Schema::table('translation_teams', function (Blueprint $t) {
                if (!Schema::hasColumn('translation_teams', 'leader_share')) {
                    $t->decimal('leader_share', 5, 2)->default(0)->after('total_earned');
                }
                if (!Schema::hasColumn('translation_teams', 'application_mode')) {
                    $t->enum('application_mode', ['invite_only', 'open', 'closed'])->default('invite_only')->after('status');
                }
                if (!Schema::hasColumn('translation_teams', 'min_trust_level')) {
                    $t->tinyInteger('min_trust_level')->default(0)->after('application_mode');
                }
                if (!Schema::hasColumn('translation_teams', 'require_portfolio')) {
                    $t->boolean('require_portfolio')->default(false)->after('min_trust_level');
                }
            });
        }

        if (Schema::hasTable('translation_team_members')) {
            Schema::table('translation_team_members', function (Blueprint $t) {
                if (!Schema::hasColumn('translation_team_members', 'note')) {
                    $t->string('note', 255)->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('translation_team_members', 'display_on_profile')) {
                    $t->boolean('display_on_profile')->default(true)->after('note');
                }
                if (!Schema::hasColumn('translation_team_members', 'can_publish')) {
                    $t->boolean('can_publish')->default(false)->after('can_invite_members');
                }
                if (!Schema::hasColumn('translation_team_members', 'can_edit_any_chapter')) {
                    $t->boolean('can_edit_any_chapter')->default(false)->after('can_publish');
                }
            });
        }

        if (!Schema::hasTable('novel_team_member_shares')) {
            Schema::create('novel_team_member_shares', function (Blueprint $t) {
                $t->id();
                $t->foreignId('novel_id')->constrained('novels')->cascadeOnDelete();
                $t->foreignId('team_id')->constrained('translation_teams')->cascadeOnDelete();
                $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $t->decimal('revenue_share', 5, 2);
                $t->string('role_override', 80)->nullable();
                $t->text('note')->nullable();
                $t->timestamps();

                $t->unique(['novel_id', 'team_id', 'user_id']);
                $t->index(['team_id', 'novel_id']);
            });
        }

        if (!Schema::hasTable('team_kick_log')) {
            Schema::create('team_kick_log', function (Blueprint $t) {
                $t->id();
                $t->foreignId('team_id')->constrained('translation_teams')->cascadeOnDelete();
                $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $t->foreignId('kicked_by')->constrained('users')->cascadeOnDelete();
                $t->enum('action', ['kick', 'leave', 'ban', 'role_change', 'share_change'])->default('kick');
                $t->string('reason', 500)->nullable();
                $t->boolean('show_in_profile')->default(false);
                $t->string('prev_role', 80)->nullable();
                $t->string('new_role', 80)->nullable();
                $t->decimal('prev_share', 5, 2)->nullable();
                $t->decimal('new_share', 5, 2)->nullable();
                $t->json('meta')->nullable();
                $t->timestamp('logged_at')->useCurrent();
                $t->timestamps();

                $t->index(['team_id', 'logged_at']);
                $t->index(['user_id', 'action']);
            });
        }

        if (!Schema::hasTable('team_applications')) {
            Schema::create('team_applications', function (Blueprint $t) {
                $t->id();
                $t->foreignId('team_id')->constrained('translation_teams')->cascadeOnDelete();
                $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $t->enum('desired_role', [
                    'translator', 'editor', 'typesetter', 'illustrator', 'tlc', 'beta', 'member',
                ])->default('member');
                $t->text('cover_letter')->nullable();
                $t->string('portfolio_url', 255)->nullable();
                $t->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending');
                $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->text('rejection_reason')->nullable();
                $t->timestamp('reviewed_at')->nullable();
                $t->timestamps();

                $t->unique(['team_id', 'user_id']);
                $t->index(['team_id', 'status']);
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('team_applications');
        Schema::dropIfExists('team_kick_log');
        Schema::dropIfExists('novel_team_member_shares');
        if (Schema::hasTable('translation_team_members')) {
            Schema::table('translation_team_members', function (Blueprint $t) {
                foreach (['note','display_on_profile','can_publish','can_edit_any_chapter'] as $col) {
                    if (Schema::hasColumn('translation_team_members', $col)) $t->dropColumn($col);
                }
            });
        }
        if (Schema::hasTable('translation_teams')) {
            Schema::table('translation_teams', function (Blueprint $t) {
                foreach (['leader_share','application_mode','min_trust_level','require_portfolio'] as $col) {
                    if (Schema::hasColumn('translation_teams', $col)) $t->dropColumn($col);
                }
            });
        }
    }
};
