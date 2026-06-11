<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('ad_banners')) {
            Schema::create('ad_banners', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('image')->nullable();
                $table->string('url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('ad_banners', function (Blueprint $table) {
                if (!Schema::hasColumn('ad_banners', 'image')) {
                    $table->string('image')->nullable();
                }
            });
        }
    }
};
