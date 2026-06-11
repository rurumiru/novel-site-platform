<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('novel_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('novel_promotion_packages')->cascadeOnDelete();
            $table->string('type', 30);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->unsignedInteger('amount_paid');
            $table->string('status', 15)->default('active');
            $table->timestamps();
            $table->index(['type', 'status', 'ends_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('novel_promotions');
    }
};
