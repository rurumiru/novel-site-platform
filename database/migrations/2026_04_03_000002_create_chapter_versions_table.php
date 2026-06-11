<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('chapter_versions')) {
            Schema::create('chapter_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->longText('content');
                $table->string('label')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();

                $table->index(['chapter_id', 'created_at']);
            });
        }
    }
};
