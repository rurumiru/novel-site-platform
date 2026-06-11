<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('ad_banners', function (Blueprint $table) {
            if (!Schema::hasColumn('ad_banners', 'url')) {
                $table->string('url')->nullable();
            }
        });
    }
};
