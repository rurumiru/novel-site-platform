<?php
namespace App\Services;

use App\Models\Chapter;
use App\Models\Novel;
use App\Services\ContentRenderer;

class ChapterImporter {

    protected static array $headerPatterns = [
        '/^(Глава\s*№?\s*\d+[^\r\n]*)/imsu',
        '/^(Chapter\s*\d+[^\r\n]*)/imsu',
        '/^(Part\s+\d+[^\r\n]*)/imsu',
        '/^(Часть\s+\d+[^\r\n]*)/imsu',
        '/^(Том\s+\d+[^\r\n]*)/imsu',
        '/^(Пролог[^\r\n]*)/imsu',
        '/^(Эпилог[^\r\n]*)/imsu',
        '/^(Интерлюдия[^\r\n]*)/imsu',
        '/^(Послесловие[^\r\n]*)/imsu',
        '/^(Предисловие[^\r\n]*)/imsu',
        '/^(\d+\.\s+[^\r\n]+)/mu',
    ];

    public static function findChapterHeaders(string $content): array {
        $found = [];
        foreach (self::$headerPatterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $m) {
                    $title = trim($m[0]);
                    if (empty($title) || mb_strlen($title) > 200) continue;
                    $offset = $m[1];
                    if (!isset($found[$offset])) {
                        $found[$offset] = ['title' => $title, 'offset' => $offset, 'length' => strlen($m[0])];
                    }
                }
            }
        }
        ksort($found);
        return array_values($found);
    }

    public static function parseChaptersFromText(string $content): array {
        $content = self::normalizeText($content);
        $headers = self::findChapterHeaders($content);

        if (empty($headers)) {
            return self::splitByBlankLines($content);
        }

        $chapters = [];
        $len = count($headers);

        $preamble = trim(substr($content, 0, $headers[0]['offset']));
        if (mb_strlen($preamble) > 100) {
            $chapters[] = ['title' => 'Предисловие', 'content' => $preamble];
        }

        for ($i = 0; $i < $len; $i++) {
            $start = $headers[$i]['offset'] + $headers[$i]['length'];
            $end = $i + 1 < $len ? $headers[$i + 1]['offset'] : strlen($content);
            $text = trim(substr($content, $start, $end - $start));
            if (empty($text)) continue;
            $chapters[] = [
                'title' => $headers[$i]['title'],
                'content' => $text,
            ];
        }

        return $chapters;
    }

    private static function splitByBlankLines(string $content): array {
        if (substr_count($content, '---') >= 2) {
            $blocks = array_filter(array_map('trim', explode('---', $content)));
        } else {
            $blocks = array_filter(array_map('trim', preg_split('/\n{3,}/', $content)));
        }

        if (count($blocks) <= 1) {
            return [['title' => 'Глава 1', 'content' => trim($content)]];
        }

        $chapters = [];
        $n = 0;
        foreach ($blocks as $block) {
            if (mb_strlen($block) < 20) continue;
            $n++;
            $lines = preg_split('/\r?\n/', $block, 2);
            if (mb_strlen($lines[0]) < 100 && isset($lines[1]) && mb_strlen(trim($lines[1])) > 50) {
                $title = trim($lines[0]);
                $body = trim($lines[1]);
            } else {
                $title = "Глава $n";
                $body = $block;
            }
            $chapters[] = ['title' => $title, 'content' => $body];
        }

        return $chapters ?: [['title' => 'Глава 1', 'content' => trim($content)]];
    }

    public static function extractTextFromFile(string $path, string $extension): string {
        $ext = strtolower($extension);

        return match ($ext) {
            'txt'           => self::extractFromTxt($path),
            'fb2'           => self::extractFromFb2($path),
            'epub'          => self::extractFromEpub($path),
            'doc', 'docx'   => self::extractFromDocx($path),
            default         => self::extractFromTxt($path),
        };
    }

    private static function extractFromTxt(string $path): string {
        $raw = file_get_contents($path);
        if (!$raw) return '';
        $enc = mb_detect_encoding($raw, ['UTF-8', 'Windows-1251', 'CP1251', 'KOI8-R', 'ISO-8859-5', 'ISO-8859-1'], true) ?: 'UTF-8';
        return mb_convert_encoding($raw, 'UTF-8', $enc);
    }

    private static function extractFromFb2(string $path): string {
        $xml = file_get_contents($path);
        if (!$xml) return '';

        $xml = preg_replace('/xmlns="[^"]+"/', '', $xml);
        $xml = preg_replace('/xmlns:l="[^"]+"/', '', $xml);

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);

        $result = '';
        $sections = $xpath->query('//body/section');

        if ($sections && $sections->length > 0) {
            foreach ($sections as $section) {
                $result .= self::fb2SectionToText($section, $xpath) . "\n\n";
            }
        } else {
            $bodies = $xpath->query('//body');
            if ($bodies && $bodies->length > 0) {
                $result = self::fb2NodeToText($bodies->item(0));
            }
        }

        libxml_clear_errors();
        return trim($result);
    }

    private static function fb2SectionToText(\DOMNode $section, \DOMXPath $xpath): string {
        $text = '';

        $titles = $xpath->query('title', $section);
        if ($titles && $titles->length > 0) {
            $titleText = trim(self::fb2NodeToText($titles->item(0)));
            if ($titleText) {
                $text .= $titleText . "\n\n";
            }
        }

        foreach ($section->childNodes as $child) {
            if ($child->nodeName === 'title') continue;
            if ($child->nodeName === 'section') {
                $text .= self::fb2SectionToText($child, $xpath) . "\n\n";
            } elseif ($child->nodeName === 'p') {
                $pText = trim(self::fb2NodeToText($child));
                if ($pText !== '') $text .= $pText . "\n\n";
            } elseif ($child->nodeName === 'empty-line') {
                $text .= "\n";
            } elseif ($child->nodeName === 'poem' || $child->nodeName === 'cite') {
                $text .= trim(self::fb2NodeToText($child)) . "\n\n";
            } elseif ($child->nodeName === 'subtitle') {
                $text .= '### ' . trim(self::fb2NodeToText($child)) . "\n\n";
            } elseif ($child->nodeName === 'epigraph') {
                $text .= '> ' . trim(self::fb2NodeToText($child)) . "\n\n";
            }
        }

        return $text;
    }

    private static function fb2NodeToText(\DOMNode $node): string {
        $text = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text .= $child->nodeValue;
            } elseif ($child->nodeName === 'p') {
                $text .= trim(self::fb2NodeToText($child)) . "\n";
            } elseif ($child->nodeName === 'strong' || $child->nodeName === 'emphasis') {
                $text .= self::fb2NodeToText($child);
            } elseif ($child->nodeName === 'v') {
                $text .= trim(self::fb2NodeToText($child)) . "\n";
            } elseif ($child->nodeName === 'stanza') {
                $text .= self::fb2NodeToText($child) . "\n";
            } else {
                $text .= self::fb2NodeToText($child);
            }
        }
        return $text;
    }

    private static function extractFromEpub(string $path): string {
        if (!class_exists(\ZipArchive::class)) {
            return self::extractFromTxt($path);
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return '';

        $container = $zip->getFromName('META-INF/container.xml');
        $opfPath = 'content.opf';
        if ($container) {
            libxml_use_internal_errors(true);
            $cDoc = new \DOMDocument();
            $cDoc->loadXML($container);
            $rootfiles = $cDoc->getElementsByTagName('rootfile');
            if ($rootfiles->length > 0) {
                $opfPath = $rootfiles->item(0)->getAttribute('full-path');
            }
            libxml_clear_errors();
        }

        $opf = $zip->getFromName($opfPath);
        if (!$opf) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with(strtolower($name), '.opf')) {
                    $opfPath = $name;
                    $opf = $zip->getFromName($name);
                    break;
                }
            }
        }

        $opfDir = dirname($opfPath);
        if ($opfDir === '.') $opfDir = '';
        else $opfDir .= '/';

        $orderedFiles = [];

        if ($opf) {
            libxml_use_internal_errors(true);
            $opfDoc = new \DOMDocument();
            $opfDoc->loadXML($opf);

            $manifest = [];
            foreach ($opfDoc->getElementsByTagName('item') as $item) {
                $id = $item->getAttribute('id');
                $href = $item->getAttribute('href');
                $mime = $item->getAttribute('media-type');
                if (str_contains($mime, 'html') || str_contains($mime, 'xml')) {
                    $manifest[$id] = $opfDir . $href;
                }
            }

            foreach ($opfDoc->getElementsByTagName('itemref') as $ref) {
                $idref = $ref->getAttribute('idref');
                if (isset($manifest[$idref])) {
                    $orderedFiles[] = $manifest[$idref];
                }
            }

            libxml_clear_errors();
        }

        if (empty($orderedFiles)) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('/\.(xhtml|html|htm)$/i', $name)) {
                    $orderedFiles[] = $name;
                }
            }
            sort($orderedFiles);
        }

        $fullText = '';
        foreach ($orderedFiles as $file) {
            $html = $zip->getFromName($file);
            if (!$html) continue;

            libxml_use_internal_errors(true);
            $doc = new \DOMDocument();
            $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);

            $body = $doc->getElementsByTagName('body')->item(0);
            if (!$body) continue;

            $sectionText = self::epubNodeToText($body);
            $sectionText = trim($sectionText);

            if (mb_strlen($sectionText) < 10) continue;

            $fullText .= $sectionText . "\n\n---\n\n";
            libxml_clear_errors();
        }

        $zip->close();
        return trim($fullText);
    }

    private static function epubNodeToText(\DOMNode $node): string {
        $text = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text .= $child->nodeValue;
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, ['h1', 'h2', 'h3', 'h4'])) {
                $heading = trim(self::epubNodeToText($child));
                if ($heading) $text .= "\n\n" . $heading . "\n\n";
            } elseif ($tag === 'p' || $tag === 'div') {
                $para = trim(self::epubNodeToText($child));
                if ($para !== '') $text .= $para . "\n\n";
            } elseif ($tag === 'br') {
                $text .= "\n";
            } elseif (in_array($tag, ['ul', 'ol'])) {
                $text .= self::epubNodeToText($child) . "\n";
            } elseif ($tag === 'li') {
                $text .= '- ' . trim(self::epubNodeToText($child)) . "\n";
            } elseif ($tag === 'blockquote') {
                $text .= '> ' . trim(self::epubNodeToText($child)) . "\n\n";
            } elseif (in_array($tag, ['em', 'i', 'strong', 'b', 'span', 'a', 'sup', 'sub'])) {
                $text .= self::epubNodeToText($child);
            } elseif ($tag === 'img') {
            } else {
                $text .= self::epubNodeToText($child);
            }
        }
        return $text;
    }

    private static function extractFromDocx(string $path): string {
        if (!class_exists(\PhpOffice\PhpWord\IOFactory::class)) return '';
        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
            $text = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    } elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $child) {
                            if (method_exists($child, 'getText')) {
                                $text .= $child->getText();
                            }
                        }
                        $text .= "\n";
                    }
                }
            }
            return trim($text);
        } catch (\Throwable $e) {
            \Log::warning('ChapterImporter DOCX error: ' . $e->getMessage());
            return '';
        }
    }

    private static function normalizeText(string $text): string {
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+$/m', '', $text);
        $text = preg_replace('/\n{4,}/', "\n\n\n", $text);
        return trim($text);
    }

    public static function importFromPath(Novel $novel, string $filePath, array $options = []): int {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $text = self::extractTextFromFile($filePath, $ext);
        return self::importFromText($novel, $text, $options);
    }

    public static function importFromText(Novel $novel, string $text, array $options = []): int {
        $chapters = self::parseChaptersFromText($text);
        $isLocked = $options['is_locked'] ?? false;
        $price = $options['price'] ?? 0;
        $volumeId = $options['volume_id'] ?? null;
        $maxOrder = $novel->chapters()->max('sort_order') ?? 0;
        $count = 0;

        foreach ($chapters as $ch) {
            if (mb_strlen($ch['content']) < 5) continue;

            $contentHtml = ContentRenderer::plainToHtml($ch['content']);
            if ($contentHtml === '') continue;

            $maxOrder++;
            Chapter::create([
                'novel_id' => $novel->id,
                'title' => mb_substr($ch['title'], 0, 255),
                'content' => $contentHtml,
                'sort_order' => $maxOrder,
                'is_published' => true,
                'is_locked' => $isLocked,
                'price' => $isLocked ? $price : 0,
                'volume_id' => $volumeId,
            ]);
            $count++;
        }
        return $count;
    }

    public static function import(Novel $novel, $filePath): int {
        return self::importFromPath($novel, $filePath, []);
    }
}
