<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('novels', 'is_adult')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->boolean('is_adult')->default(false);
            });
        }
        if (!Schema::hasColumn('users', 'birth_date')) {
            Schema::table('users', function (Blueprint $table) {
                $table->date('birth_date')->nullable();
            });
        }
    }
};
