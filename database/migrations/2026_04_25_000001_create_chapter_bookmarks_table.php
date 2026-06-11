<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('chapter_bookmarks')) {
            Schema::create('chapter_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
                $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
                $table->decimal('percent', 5, 2)->default(0);
                $table->string('note', 280)->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'chapter_id']);
                $table->index(['user_id', 'novel_id']);
            });
        }
    }
    public function down(): void { Schema::dropIfExists('chapter_bookmarks'); }
};
