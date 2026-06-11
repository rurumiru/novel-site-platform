<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('users', 'can_create_novels')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('can_create_novels')->default(false);
            });
        }
        
        DB::table('users')->where('id', 1)->update(['can_create_novels' => true]);
    }
};
