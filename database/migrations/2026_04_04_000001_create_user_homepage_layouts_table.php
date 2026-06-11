<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('user_homepage_layouts')) {
            Schema::create('user_homepage_layouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->json('layout');
                $table->timestamps();
                $table->unique('user_id');
            });
        }
    }
};
