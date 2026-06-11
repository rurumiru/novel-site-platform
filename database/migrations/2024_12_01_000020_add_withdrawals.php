<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('withdrawals')) {
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();
                $table->string('uid')->unique();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->text('details')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('users', 'can_withdraw')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('can_withdraw')->default(false);
            });
        }

        DB::table('settings')->insertOrIgnore([
            ['key' => 'enable_new_design', 'value' => '1'],
        ]);
    }
};
