<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('novels', function (Blueprint $table) {
            $table->unsignedSmallInteger('auto_unlock_interval_days')->nullable()->after('next_chapter_at');
            $table->timestamp('auto_unlock_last_at')->nullable()->after('auto_unlock_interval_days');
        });
    }

    public function down(): void
    {
        Schema::table('novels', function (Blueprint $table) {
            $table->dropColumn(['auto_unlock_interval_days', 'auto_unlock_last_at']);
        });
    }
};
