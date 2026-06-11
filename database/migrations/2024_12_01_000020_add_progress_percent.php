<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('reading_progress', 'percent')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->float('percent')->default(0);
            });
        }
    }
};
