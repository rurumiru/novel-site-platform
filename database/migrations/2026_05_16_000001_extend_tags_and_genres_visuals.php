<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('tags')) {
            Schema::table('tags', function (Blueprint $table) {
                if (!Schema::hasColumn('tags', 'category')) {
                    $table->string('category', 60)->nullable()->after('description')
                        ->comment('Группа/категория тега для удобства в каталоге');
                }
                if (!Schema::hasColumn('tags', 'color')) {
                    $table->string('color', 9)->nullable()->after('category')
                        ->comment('HEX-цвет бейджа (#rrggbb)');
                }
                if (!Schema::hasColumn('tags', 'icon')) {
                    $table->string('icon', 80)->nullable()->after('color')
                        ->comment('Класс Font Awesome (например, fa-solid fa-fire)');
                }
            });
        }

        if (Schema::hasTable('genres')) {
            Schema::table('genres', function (Blueprint $table) {
                if (!Schema::hasColumn('genres', 'description')) {
                    $table->string('description', 500)->nullable()->after('slug');
                }
                if (!Schema::hasColumn('genres', 'color')) {
                    $table->string('color', 9)->nullable()->after('description');
                }
                if (!Schema::hasColumn('genres', 'icon')) {
                    $table->string('icon', 80)->nullable()->after('color');
                }
                if (!Schema::hasColumn('genres', 'is_adult')) {
                    $table->boolean('is_adult')->default(false)->after('icon');
                }
                if (!Schema::hasColumn('genres', 'is_visible')) {
                    $table->boolean('is_visible')->default(true)->after('is_adult');
                }
                if (!Schema::hasColumn('genres', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_visible');
                }
            });
        }
    }

    public function down(): void {
        if (Schema::hasTable('tags')) {
            Schema::table('tags', function (Blueprint $table) {
                foreach (['icon', 'color', 'category'] as $col) {
                    if (Schema::hasColumn('tags', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('genres')) {
            Schema::table('genres', function (Blueprint $table) {
                foreach (['sort_order', 'is_visible', 'is_adult', 'icon', 'color', 'description'] as $col) {
                    if (Schema::hasColumn('genres', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
