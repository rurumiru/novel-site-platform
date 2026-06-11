<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('novels', 'background_image')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->string('background_image')->nullable();
            });
        }

        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
            
            Schema::create('novel_tag', function (Blueprint $table) {
                $table->id();
                $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });
            
            DB::table('settings')->insertOrIgnore([
                ['key' => 'site_name', 'value' => 'ER.IIIBA'],
                ['key' => 'footer_text', 'value' => 'Лучшая платформа для чтения.'],
                ['key' => 'vk_link', 'value' => '#'],
                ['key' => 'telegram_link', 'value' => '#'],
                ['key' => 'discord_link', 'value' => '#'],
            ]);
        }
    }
};
