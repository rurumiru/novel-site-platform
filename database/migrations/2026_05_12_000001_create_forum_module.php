<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forum_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('description', 500)->nullable();
            $t->string('icon', 64)->nullable();
            $t->string('color', 16)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['is_active', 'sort_order']);
        });

        Schema::create('forum_sections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained('forum_categories')->cascadeOnDelete();
            $t->string('name');
            $t->string('slug');
            $t->string('description', 500)->nullable();
            $t->string('icon', 64)->nullable();
            $t->string('accent_color', 16)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->string('min_role', 32)->nullable();
            $t->string('create_policy', 32)->default('auth');
            $t->timestamps();
            $t->unique(['category_id', 'slug']);
            $t->index(['is_active', 'sort_order']);
        });

        Schema::create('forum_threads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('section_id')->constrained('forum_sections')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->unsignedBigInteger('novel_id')->nullable()->index();
            $t->unsignedBigInteger('chapter_id')->nullable()->index();
            $t->string('title');
            $t->string('slug', 255);
            $t->mediumText('body')->nullable();
            $t->mediumText('body_html')->nullable();
            $t->enum('visibility', ['public', 'private_users', 'private_roles'])->default('public');
            $t->json('allowed_roles')->nullable();
            $t->boolean('is_pinned')->default(false);
            $t->boolean('is_locked')->default(false);
            $t->unsignedInteger('views_count')->default(0);
            $t->unsignedInteger('posts_count')->default(0);
            $t->unsignedInteger('reactions_count')->default(0);
            $t->timestamp('last_post_at')->nullable();
            $t->unsignedBigInteger('last_post_user_id')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['section_id', 'is_pinned', 'last_post_at']);
            $t->index(['novel_id', 'chapter_id']);
            $t->index('last_post_at');
            $t->foreign('novel_id')->references('id')->on('novels')->nullOnDelete();
            $t->foreign('chapter_id')->references('id')->on('chapters')->nullOnDelete();
        });

        Schema::create('forum_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->mediumText('body');
            $t->mediumText('body_html')->nullable();
            $t->boolean('is_first')->default(false);
            $t->timestamp('edited_at')->nullable();
            $t->unsignedBigInteger('edited_by')->nullable();
            $t->unsignedInteger('reactions_count')->default(0);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['thread_id', 'created_at']);
            $t->index('parent_id');
            $t->foreign('parent_id')->references('id')->on('forum_posts')->nullOnDelete();
        });

        Schema::create('forum_tags', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('color', 16)->nullable();
            $t->unsignedInteger('usage_count')->default(0);
            $t->timestamps();
        });

        Schema::create('forum_thread_tag', function (Blueprint $t) {
            $t->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained('forum_tags')->cascadeOnDelete();
            $t->primary(['thread_id', 'tag_id']);
        });

        Schema::create('forum_reactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('reactable_type');
            $t->unsignedBigInteger('reactable_id');
            $t->string('emoji', 32);
            $t->timestamps();
            $t->index(['reactable_type', 'reactable_id']);
            $t->unique(['user_id', 'reactable_type', 'reactable_id', 'emoji'], 'forum_reactions_unique');
        });

        Schema::create('forum_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('reportable_type');
            $t->unsignedBigInteger('reportable_id');
            $t->string('reason', 64);
            $t->string('comment', 1000)->nullable();
            $t->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $t->timestamp('resolved_at')->nullable();
            $t->unsignedBigInteger('resolved_by')->nullable();
            $t->timestamps();
            $t->index(['reportable_type', 'reportable_id']);
            $t->index('status');
        });

        Schema::create('forum_participants', function (Blueprint $t) {
            $t->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->boolean('can_post')->default(true);
            $t->timestamps();
            $t->primary(['thread_id', 'user_id']);
        });

        Schema::create('forum_subscriptions', function (Blueprint $t) {
            $t->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->boolean('notify')->default(true);
            $t->timestamps();
            $t->primary(['thread_id', 'user_id']);
        });

        Schema::create('forum_thread_reads', function (Blueprint $t) {
            $t->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->unsignedBigInteger('last_read_post_id')->nullable();
            $t->timestamp('last_read_at');
            $t->primary(['thread_id', 'user_id']);
            $t->index('last_read_at');
        });

        Schema::create('forum_action_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('action', 64);
            $t->string('subject_type')->nullable();
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->json('meta')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
            $t->index(['subject_type', 'subject_id']);
            $t->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_action_logs');
        Schema::dropIfExists('forum_thread_reads');
        Schema::dropIfExists('forum_subscriptions');
        Schema::dropIfExists('forum_participants');
        Schema::dropIfExists('forum_reports');
        Schema::dropIfExists('forum_reactions');
        Schema::dropIfExists('forum_thread_tag');
        Schema::dropIfExists('forum_tags');
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_threads');
        Schema::dropIfExists('forum_sections');
        Schema::dropIfExists('forum_categories');
    }
};
