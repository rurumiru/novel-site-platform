<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('image')->nullable();
                $table->longText('content');
                $table->boolean('is_published')->default(true);
                $table->integer('views')->default(0);
                $table->timestamps();
            });
        }
    }
};
