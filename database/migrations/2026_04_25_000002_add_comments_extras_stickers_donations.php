<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('comment_recommendations')) {
            Schema::create('comment_recommendations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['comment_id', 'user_id']);
                $table->index('user_id');
            });
        }

        if (!Schema::hasTable('comment_reports')) {
            Schema::create('comment_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('reason', 255);
                $table->text('details')->nullable();
                $table->string('status', 20)->default('new');
                $table->timestamps();
                $table->unique(['comment_id', 'user_id']);
                $table->index('status');
            });
        }

        if (!Schema::hasTable('sticker_packs')) {
            Schema::create('sticker_packs', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('slug', 120)->unique();
                $table->string('cover_image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stickers')) {
            Schema::create('stickers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pack_id')->nullable()->constrained('sticker_packs')->nullOnDelete();
                $table->string('name', 120)->nullable();
                $table->string('slug', 140)->unique();
                $table->string('image_path');
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->index(['pack_id', 'sort_order']);
            });
        }

        if (!Schema::hasTable('donations')) {
            Schema::create('donations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('novel_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->string('message', 280)->nullable();
                $table->boolean('is_anonymous')->default(false);
                $table->string('status', 20)->default('completed');
                $table->timestamps();
                $table->index(['novel_id', 'created_at']);
                $table->index(['author_id', 'created_at']);
            });
        }

        if (!Schema::hasColumn('chapters', 'highlight')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->string('highlight', 30)->nullable()->after('is_locked');
                $table->index('highlight');
            });
        }
    }

    public function down(): void {
        if (Schema::hasColumn('chapters', 'highlight')) {
            Schema::table('chapters', function (Blueprint $t) {
                $t->dropIndex(['highlight']);
                $t->dropColumn('highlight');
            });
        }
        Schema::dropIfExists('donations');
        Schema::dropIfExists('stickers');
        Schema::dropIfExists('sticker_packs');
        Schema::dropIfExists('comment_reports');
        Schema::dropIfExists('comment_recommendations');
    }
};
