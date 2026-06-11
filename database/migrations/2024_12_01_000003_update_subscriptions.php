<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'uid')) {
                $table->string('uid')->nullable()->after('id');
            }
            if (!Schema::hasColumn('subscriptions', 'type')) {
                $table->string('type')->default('single')->after('uid');
            }
            if (!Schema::hasColumn('subscriptions', 'author_id')) {
                $table->foreignId('author_id')->nullable()->after('user_id')->constrained('users')->cascadeOnDelete();
            }
            $table->foreignId('novel_id')->nullable()->change();
        });
    }
};
