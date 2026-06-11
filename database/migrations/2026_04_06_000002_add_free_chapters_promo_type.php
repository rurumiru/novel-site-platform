<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE promo_codes MODIFY COLUMN type ENUM('balance','discount','free_novel','free_chapters') NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE promo_codes DROP CONSTRAINT IF EXISTS promo_codes_type_check");
            DB::statement("ALTER TABLE promo_codes ADD CONSTRAINT promo_codes_type_check CHECK (type IN ('balance','discount','free_novel','free_chapters'))");
        }

        if (!Schema::hasColumn('promo_codes', 'chapters_count')) {
            Schema::table('promo_codes', function (Blueprint $table) {
                $table->unsignedInteger('chapters_count')->nullable()->after('value');
            });
        }
    }

    public function down(): void
    {
        DB::table('promo_codes')->where('type', 'free_chapters')->delete();

        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE promo_codes MODIFY COLUMN type ENUM('balance','discount','free_novel') NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE promo_codes DROP CONSTRAINT IF EXISTS promo_codes_type_check");
            DB::statement("ALTER TABLE promo_codes ADD CONSTRAINT promo_codes_type_check CHECK (type IN ('balance','discount','free_novel'))");
        }

        if (Schema::hasColumn('promo_codes', 'chapters_count')) {
            Schema::table('promo_codes', function (Blueprint $table) {
                $table->dropColumn('chapters_count');
            });
        }
    }
};
