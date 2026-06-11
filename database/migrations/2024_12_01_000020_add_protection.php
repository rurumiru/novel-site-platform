<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('novels', 'is_protected')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->boolean('is_protected')->default(false)->comment('Защита от копирования');
            });
        }
    }
};
