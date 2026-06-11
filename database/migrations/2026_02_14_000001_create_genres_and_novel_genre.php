<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('genres')) {
            Schema::create('genres', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('novel_genre')) {
            Schema::create('novel_genre', function (Blueprint $table) {
                $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
                $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
                $table->primary(['novel_id', 'genre_id']);
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('novel_genre');
        Schema::dropIfExists('genres');
    }
};
