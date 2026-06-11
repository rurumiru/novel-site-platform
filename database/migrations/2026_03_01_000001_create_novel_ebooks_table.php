<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('novel_ebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
            $table->string('format', 10);
            $table->string('access_type', 10);
            $table->string('file_path');
            $table->char('chapters_hash', 32);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['novel_id', 'format', 'access_type']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('novel_ebooks');
    }
};
