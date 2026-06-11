<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('stats_snapshots')) {
            Schema::create('stats_snapshots', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->unsignedBigInteger('entity_id')->nullable()->index();
                $table->date('date');
                $table->tinyInteger('hour')->nullable();
                $table->unsignedInteger('views')->default(0);
                $table->unsignedInteger('unique_visitors')->default(0);
                $table->unsignedInteger('favorites')->default(0);
                $table->unsignedInteger('comments')->default(0);
                $table->unsignedInteger('chapter_purchases')->default(0);
                $table->decimal('revenue', 10, 2)->default(0);
                $table->unsignedInteger('new_chapters')->default(0);
                $table->unsignedInteger('ratings_count')->default(0);
                $table->decimal('rating_avg', 3, 1)->default(0);
                $table->timestamps();

                $table->unique(['type', 'entity_id', 'date', 'hour']);
                $table->index(['type', 'entity_id', 'date']);
            });
        }
    }
};
