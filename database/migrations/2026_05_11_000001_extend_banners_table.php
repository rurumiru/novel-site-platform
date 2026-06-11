<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'image_path_mobile')) {
                $table->string('image_path_mobile')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('banners', 'subtitle')) {
                $table->string('subtitle', 120)->nullable()->after('title');
            }
            if (!Schema::hasColumn('banners', 'cta_text')) {
                $table->string('cta_text', 60)->nullable()->after('link');
            }
            if (!Schema::hasColumn('banners', 'badge')) {
                $table->string('badge', 40)->nullable()->after('cta_text');
            }
            if (!Schema::hasColumn('banners', 'theme')) {
                $table->string('theme', 16)->default('dark')->after('badge');
            }
            if (!Schema::hasColumn('banners', 'align')) {
                $table->string('align', 16)->default('left')->after('theme');
            }
        });
    }

    public function down(): void {
        Schema::table('banners', function (Blueprint $table) {
            foreach (['image_path_mobile', 'subtitle', 'cta_text', 'badge', 'theme', 'align'] as $col) {
                if (Schema::hasColumn('banners', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
