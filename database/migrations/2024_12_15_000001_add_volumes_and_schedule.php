<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('volumes')) {
            Schema::create('volumes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        Schema::table('chapters', function (Blueprint $table) {
            if (!Schema::hasColumn('chapters', 'volume_id')) {
                $table->foreignId('volume_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('chapters', 'published_at')) {
                $table->timestamp('published_at')->nullable();
            }
        });
    }
};
