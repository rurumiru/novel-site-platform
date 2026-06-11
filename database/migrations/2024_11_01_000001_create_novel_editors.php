<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('novel_editors')) {
            Schema::create('novel_editors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('novel_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                
                $table->unique(['novel_id', 'user_id']);
            });
        }
    }
};
