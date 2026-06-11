<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = ['reviews', 'posts', 'forum_threads', 'forum_posts'];

    public function up(): void {
        foreach ($this->tables as $name) {
            if (Schema::hasTable($name) && !Schema::hasColumn($name, 'is_demo')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->boolean('is_demo')->default(false)->index();
                });
            }
        }
    }

    public function down(): void {
        foreach ($this->tables as $name) {
            if (Schema::hasTable($name) && Schema::hasColumn($name, 'is_demo')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropColumn('is_demo');
                });
            }
        }
    }
};
