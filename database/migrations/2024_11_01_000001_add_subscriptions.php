<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('novels', 'price')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->default(0.00);
                $table->text('subscription_info')->nullable();
            });
        }

        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount_paid', 10, 2);
                $table->timestamp('expires_at')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }
    }
};
