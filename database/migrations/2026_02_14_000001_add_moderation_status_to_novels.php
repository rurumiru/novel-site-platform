<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('novels', 'moderation_status')) {
            Schema::table('novels', function (Blueprint $table) {
                $table->string('moderation_status', 20)->default('approved')->after('is_published');
                $table->unsignedBigInteger('moderated_by')->nullable()->after('moderation_status');
                $table->timestamp('moderated_at')->nullable()->after('moderated_by');
            });
        }
    }

    public function down(): void {
        Schema::table('novels', function (Blueprint $table) {
            $table->dropColumn(['moderation_status', 'moderated_by', 'moderated_at']);
        });
    }
};
