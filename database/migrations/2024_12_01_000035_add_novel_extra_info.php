<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('novels', function (Blueprint $table) {
            if (!Schema::hasColumn('novels', 'original_source')) $table->string('original_source')->nullable();
            if (!Schema::hasColumn('novels', 'original_author')) $table->string('original_author')->nullable();
        });
    }
};
