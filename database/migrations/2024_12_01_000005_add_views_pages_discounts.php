<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('novel_views')) {
            Schema::create('novel_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
                $table->string('ip_address', 45);
                $table->string('user_agent')->nullable();
                $table->date('viewed_at');
                $table->unique(['novel_id', 'ip_address', 'viewed_at']);
            });
        }

        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->longText('content');
                $table->boolean('is_published')->default(true);
                $table->timestamps();
            });
            
            DB::table('pages')->insertOrIgnore([
                ['title' => 'Оферта', 'slug' => 'offer', 'content' => '<h1>Публичная оферта</h1><p>Текст оферты...</p>', 'is_published' => true],
                ['title' => 'О нас', 'slug' => 'about', 'content' => '<h1>О нас</h1><p>Мы лучшая платформа...</p>', 'is_published' => true],
            ]);
        }

        if (!Schema::hasColumn('novels', 'discount_price')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            });
        }
    }
};
