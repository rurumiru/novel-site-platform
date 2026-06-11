<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('comments', 'parent_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('comment_likes')) {
            Schema::create('comment_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'comment_id']);
            });
        }
    }
};
