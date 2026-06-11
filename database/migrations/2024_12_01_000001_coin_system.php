<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('users', 'balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('balance')->default(0);
            });
        }

        if (!Schema::hasColumn('chapters', 'price')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->integer('price')->default(0);
            });
        }

        if (!Schema::hasTable('user_unlocked_chapters')) {
            Schema::create('user_unlocked_chapters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
                $table->integer('price_paid')->default(0);
                $table->timestamps();
                
                $table->unique(['user_id', 'chapter_id']);
            });
        }
    }
};
