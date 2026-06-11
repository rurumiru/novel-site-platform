<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'banner_image')) {
                $table->string('banner_image')->nullable();
            }
            if (!Schema::hasColumn('users', 'can_set_banner')) {
                $table->boolean('can_set_banner')->default(false);
            }
        });
        
        DB::table('users')->where('id', 1)->update(['can_set_banner' => true]);
    }
};
