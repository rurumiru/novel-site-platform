<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('chapter_likes')) {
            Schema::create('chapter_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'chapter_id']);
            });
        }
    }
};
