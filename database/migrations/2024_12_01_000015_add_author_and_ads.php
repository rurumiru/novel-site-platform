<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('novels', 'author_name')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->string('author_name')->nullable()->after('title');
            });
        }

        if (!Schema::hasTable('ad_banners')) {
            Schema::create('ad_banners', function (Blueprint $table) {
                $table->id();
                $table->string('image_path')->nullable();
                $table->string('title')->nullable();
                $table->text('text')->nullable();
                $table->string('link')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('position')->default('home_middle');
                $table->timestamps();
            });
        }
        
        DB::table('settings')->insertOrIgnore([
            ['key' => 'show_social_modal', 'value' => '1'],
            ['key' => 'social_modal_title', 'value' => 'Присоединяйтесь к нам!'],
            ['key' => 'social_modal_text', 'value' => 'Следите за новостями в наших соцсетях.'],
        ]);
    }
};
