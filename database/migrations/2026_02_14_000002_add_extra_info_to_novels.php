<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('novels', function (Blueprint $table) {
            if (!Schema::hasColumn('novels', 'extra_info')) {
                $table->text('extra_info')->nullable()->after('description');
            }
        });
    }
};
