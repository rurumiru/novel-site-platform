<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('users', 'donation_link')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('donation_link')->nullable();
                $table->boolean('is_donation_link_approved')->default(false);
            });
        }
    }
};
