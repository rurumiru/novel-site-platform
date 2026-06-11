<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
                $table->text('bio_pending')->nullable();
            }
        });
        
        DB::table('settings')->insertOrIgnore([
            ['key' => 'wallet_info', 'value' => 'Карта: 0000 0000 0000 0000 (Сбер)'],
            ['key' => 'admin_contact', 'value' => 'https://t.me/admin'],
        ]);
    }
};
