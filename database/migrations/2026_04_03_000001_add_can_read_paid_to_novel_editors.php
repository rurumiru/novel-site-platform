<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('novel_editors') && !Schema::hasColumn('novel_editors', 'can_read_paid')) {
            Schema::table('novel_editors', function (Blueprint $table) {
                $table->boolean('can_read_paid')->default(false)->after('user_id');
            });
        }
    }
};
