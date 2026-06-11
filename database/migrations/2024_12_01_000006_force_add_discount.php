<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('novels', 'discount_price')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            });
        }
    }
};
