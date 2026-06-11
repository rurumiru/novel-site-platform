<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('tags')) {
            return;
        }

        Schema::table('tags', function (Blueprint $table) {
            if (!Schema::hasColumn('tags', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('tags', 'description')) {
                $table->string('description', 500)->nullable()->after('slug');
            }
            if (!Schema::hasColumn('tags', 'is_adult')) {
                $table->boolean('is_adult')->default(false)->after('description')
                    ->comment('Тег помечает контент как 18+');
            }
            if (!Schema::hasColumn('tags', 'is_restricted')) {
                $table->boolean('is_restricted')->default(false)->after('is_adult')
                    ->comment('Тег запрещён в РФ — все новеллы с этим тегом блокируются геофильтром');
            }
            if (!Schema::hasColumn('tags', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('is_restricted')
                    ->comment('Показывать тег в каталоге / на странице новеллы');
            }
            if (!Schema::hasColumn('tags', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_visible');
            }
        });

        try {
            $rows = \DB::table('tags')->whereNull('slug')->orWhere('slug', '')->get();
            foreach ($rows as $row) {
                $base = \Illuminate\Support\Str::slug($row->name) ?: ('tag-' . $row->id);
                $slug = $base;
                $i = 2;
                while (\DB::table('tags')->where('slug', $slug)->where('id', '!=', $row->id)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }
                \DB::table('tags')->where('id', $row->id)->update(['slug' => $slug]);
            }
        } catch (\Throwable $e) {
        }
    }

    public function down(): void {
        if (!Schema::hasTable('tags')) return;

        Schema::table('tags', function (Blueprint $table) {
            foreach (['sort_order','is_visible','is_restricted','is_adult','description','slug'] as $col) {
                if (Schema::hasColumn('tags', $col)) {
                    if ($col === 'slug') {
                        try { $table->dropUnique(['slug']); } catch (\Throwable $e) {}
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
