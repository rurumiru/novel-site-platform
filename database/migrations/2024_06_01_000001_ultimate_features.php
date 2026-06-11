<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('users', 'novel_limit')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('novel_limit')->default(0);
            });
        }

        if (!Schema::hasTable('reading_progress')) {
            Schema::create('reading_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
                $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
                
                $table->unique(['user_id', 'novel_id', 'chapter_id']);
            });
        }
    }
};
