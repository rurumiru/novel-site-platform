<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('users', 'social_link')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('social_link')->nullable();
            });
        }

        if (!Schema::hasColumn('chapters', 'is_locked')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->boolean('is_locked')->default(false);
            });
        }

        if (!Schema::hasTable('novel_accesses')) {
            Schema::create('novel_accesses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
                $table->boolean('is_full_access')->default(false);
                $table->integer('chapters_count')->default(0);
                $table->timestamps();
                
                $table->unique(['user_id', 'novel_id']);
            });
        }
    }
};
