<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('novels', function (Blueprint $table) {
            if (!Schema::hasColumn('novels', 'release_year')) $table->integer('release_year')->nullable();
            if (!Schema::hasColumn('novels', 'country')) $table->string('country')->nullable();
            if (!Schema::hasColumn('novels', 'alternative_names')) $table->text('alternative_names')->nullable();
        });
    }
};
