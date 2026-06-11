<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('plus_plans')) {
            Schema::create('plus_plans', function (Blueprint $table) {
                $table->id();
                $table->string('key', 50)->unique();
                $table->string('name', 100);
                $table->string('short_label', 80)->nullable();
                $table->unsignedInteger('days');
                $table->decimal('price', 10, 2)->nullable();
                $table->string('currency', 8)->default('RUB');
                $table->boolean('is_recurring')->default(false);
                $table->json('features')->nullable();
                $table->unsignedInteger('discount_percent')->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'plus_plan_id')) {
                $table->foreignId('plus_plan_id')->nullable()->after('author_id')->constrained('plus_plans')->nullOnDelete();
            }
        });
    }

    public function down(): void {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'plus_plan_id')) {
                $table->dropForeign(['plus_plan_id']);
                $table->dropColumn('plus_plan_id');
            }
        });
        Schema::dropIfExists('plus_plans');
    }
};
