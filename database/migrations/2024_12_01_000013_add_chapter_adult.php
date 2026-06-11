<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('chapters', 'is_adult')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->boolean('is_adult')->default(false);
            });
        }
    }
};
