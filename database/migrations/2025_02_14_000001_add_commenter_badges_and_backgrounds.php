<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('commenter_badges')) {
            Schema::create('commenter_badges', function (Blueprint $table) {
                $table->id();
                $table->string('title', 50);
                $table->string('icon', 50)->nullable();
                $table->string('color', 20)->default('indigo');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('commenter_backgrounds')) {
            Schema::create('commenter_backgrounds', function (Blueprint $table) {
                $table->id();
                $table->string('title', 50);
                $table->string('css_class', 100);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'commenter_badge_id')) {
                $table->foreignId('commenter_badge_id')->nullable()->constrained('commenter_badges')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'commenter_background_id')) {
                $table->foreignId('commenter_background_id')->nullable()->constrained('commenter_backgrounds')->nullOnDelete();
            }
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'commenter_badge_id')) {
                $table->dropForeign(['commenter_badge_id']);
            }
            if (Schema::hasColumn('users', 'commenter_background_id')) {
                $table->dropForeign(['commenter_background_id']);
            }
        });
        Schema::dropIfExists('commenter_backgrounds');
        Schema::dropIfExists('commenter_badges');
    }
};
