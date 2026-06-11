<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->string('type')->default('deposit');
                $table->string('status')->default('pending');
                $table->string('contact_info')->nullable();
                $table->string('uid')->unique();
                $table->timestamps();
            });
        }
        
        DB::table('settings')->insertOrIgnore([
            ['key' => 'bundle_price', 'value' => '450'],
            ['key' => 'bundle_enabled', 'value' => '1']
        ]);
    }
};
