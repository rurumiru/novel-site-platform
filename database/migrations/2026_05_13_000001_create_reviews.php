<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('novel_id')->nullable()->constrained()->nullOnDelete();
                $table->enum('category', ['review', 'promotion', 'notice'])->default('review')->index();
                $table->string('title', 200);
                $table->longText('body');
                $table->unsignedInteger('views_count')->default(0);
                $table->unsignedInteger('recommends_count')->default(0);
                $table->unsignedInteger('comments_count')->default(0);
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_published')->default(true);
                $table->timestamp('last_activity_at')->nullable()->index();
                $table->timestamps();

                $table->index(['category', 'is_pinned', 'last_activity_at']);
                $table->index(['novel_id']);
            });
        }

        if (!Schema::hasTable('review_recommends')) {
            Schema::create('review_recommends', function (Blueprint $table) {
                $table->id();
                $table->foreignId('review_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['review_id', 'user_id']);
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('review_recommends');
        Schema::dropIfExists('reviews');
    }
};
