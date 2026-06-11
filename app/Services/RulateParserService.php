<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Novel;
use App\Models\Chapter;
use App\Models\Tag;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RulateParserService {
    
    public function parse($url, $userId) {
        $data = $this->parseInfo($url);
        if (!$data) return false;

        $novel = Novel::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'author_name' => $data['author_name'],
            'description' => $data['description'],
            'status' => 'ongoing',
            'is_published' => false,
        ]);

        if (!empty($data['tags'])) {
            $novel->tags()->sync($data['tags']);
        }

        try {
            $response = Http::get($url);
            $html = $response->body();
            $crawler = new Crawler($html);

            $crawler->filter('.chapter-row a')->each(function (Crawler $node) use ($novel) {
                $chapterUrl = 'http://localhost' . $node->attr('href');
                $chapterTitle = $node->text();
                
                try {
                    $chResponse = Http::get($chapterUrl);
                    if ($chResponse->successful()) {
                        $chCrawler = new Crawler($chResponse->body());
                        $content = $chCrawler->filter('.content-text')->html();
                        
                        Chapter::create([
                            'novel_id' => $novel->id,
                            'title' => $chapterTitle,
                            'content' => $content,
                            'sort_order' => $novel->chapters()->count() + 1,
                            'is_published' => true
                        ]);
                    }
                } catch (\Exception $e) {}
            });
        } catch (\Exception $e) {}

        return $novel;
    }

    public function parseInfo($url) {
        try {
            $response = Http::get($url);
            if ($response->failed()) return null;
            
            $html = $response->body();
            $crawler = new Crawler($html);
            
            $title = $crawler->filter('h1')->count() ? $crawler->filter('h1')->text() : 'Без названия';
            $description = $crawler->filter('.book-description')->count() ? $crawler->filter('.book-description')->html() : '';
            $author = $crawler->filter('a[href*="/author/"]')->count() ? $crawler->filter('a[href*="/author/"]')->first()->text() : null;
            
            $tags = $crawler->filter('a[href*="/tags/"]')->each(function (Crawler $node) {
                return trim($node->text());
            });
            $tagIds = [];
            foreach ($tags as $tagName) {
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
            }

            return [
                'title' => $title,
                'author_name' => $author,
                'description' => $description,
                'tags' => $tagIds,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
