<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Novel;

class SyncNovelStats extends Command {
    protected $signature = 'novel:sync';
    protected $description = 'Sync chapters count and likes';

    public function handle() {
        $novels = Novel::all();
        foreach ($novels as $novel) {
            $count = $novel->chapters()->where('is_published', true)->count();

            $novel->touch();
            
            $this->info("Novel {$novel->id}: {$count} chapters.");
        }
        $this->info('Done.');
    }
}
