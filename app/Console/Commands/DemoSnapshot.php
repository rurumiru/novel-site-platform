<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoSnapshot extends Command
{
    protected $signature = 'demo:snapshot {--users : Также пометить всех текущих пользователей как демо}';
    protected $description = 'Фиксирует текущий контент как эталон демо-стенда (is_demo=true). Запускается один раз после заливки демо-данных.';

    public function handle(): int
    {
        $marked = 0;

        foreach ((array) config('demo.flagged_tables', []) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'is_demo')) {
                $marked += DB::table($table)->where('is_demo', false)->update(['is_demo' => true]);
            }
        }

        if ($this->option('users') && Schema::hasColumn('users', 'is_demo')) {
            $marked += DB::table('users')->where('is_demo', false)->update(['is_demo' => true]);
            $this->info('Все текущие пользователи помечены как демо.');
        }

        $this->info("Снимок эталона создан. Помечено как демо записей: {$marked}.");
        $this->line('Теперь весь новый контент посетителей (is_demo=false) будет удаляться командой demo:cleanup.');
        return self::SUCCESS;
    }
}
