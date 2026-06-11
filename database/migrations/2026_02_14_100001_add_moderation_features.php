<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('messages', 'is_spam')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->boolean('is_spam')->default(false);
                $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('spam_reported_at')->nullable();
            });
        }
        if (!Schema::hasColumn('novels', 'cover_moderation_status')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->string('cover_moderation_status')->nullable();
                $table->string('description_moderation_status')->nullable();
            });
        }
    }
};
