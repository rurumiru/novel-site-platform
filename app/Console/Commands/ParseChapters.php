<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use App\Models\Novel;
use App\Models\Chapter;
use App\Models\Volume;

class ParseChapters extends Command {
    protected $signature = 'parse:chapters {novel_id} {url}';
    protected $description = 'Parse chapters from a Rulate URL for a specific novel';

    public function handle() {
        $novelId = $this->argument('novel_id');
        $url = $this->argument('url');
        $novel = Novel::find($novelId);

        if (!$novel) {
            $this->error("Novel with ID {$novelId} not found.");
            return 1;
        }

        $client = new Client();
        $crawler = $client->request('GET', $url);

        $this->info("Parsing novel: {$novel->title}");

        $crawler->filter('.chapter-item')->each(function ($node) use ($novel) {
            $title = $node->filter('a')->text();
            $chapterUrl = $node->filter('a')->attr('href');
            
            if (Chapter::where('title', $title)->where('novel_id', $novel->id)->exists()) {
                $this->warn("Chapter '{$title}' already exists. Skipping.");
                return;
            }

            $this->info("Parsing chapter: {$title}");
            
            $client = new Client();
            $chapterCrawler = $client->request('GET', $chapterUrl);
            
            $content = '';
            $chapterCrawler->filter('.content-text p')->each(function ($pNode) use (&$content) {
                $content .= '<p>' . $pNode->html() . '</p>';
            });

            Chapter::create([
                'novel_id' => $novel->id,
                'title' => $title,
                'content' => $content,
                'sort_order' => Chapter::where('novel_id', $novel->id)->max('sort_order') + 1,
                'is_published' => true,
            ]);
        });

        $this->info('Parsing complete!');
        return 0;
    }
}
