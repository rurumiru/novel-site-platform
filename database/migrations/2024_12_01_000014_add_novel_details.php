<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('novels', function (Blueprint $table) {
            if (!Schema::hasColumn('novels', 'original_author')) $table->string('original_author')->nullable();
            if (!Schema::hasColumn('novels', 'source_link')) $table->string('source_link')->nullable();
            if (!Schema::hasColumn('novels', 'show_chapter_banner')) $table->boolean('show_chapter_banner')->default(true);
        });
    }
};
