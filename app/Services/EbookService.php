<?php
namespace App\Services;

use App\Models\Novel;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class EbookService {

    private function numberedTitle(int $num, ?string $title): string {
        $title = trim((string) $title);
        if ($title === '') return "Глава {$num}";
        if (preg_match('/^(Глава|Chapter|Пролог|Эпилог|Prologue|Epilogue)\b/ui', $title)) return $title;
        if (preg_match('/^\d+\s*[.)]/u', $title)) return $title;
        return "Глава {$num}. {$title}";
    }

    private function getImageData($path) {
        try {
            $content = null;
            $mime = 'image/jpeg';

            if (filter_var($path, FILTER_VALIDATE_URL)) {
                $response = Http::timeout(5)->get($path);
                if ($response->successful()) {
                    $content = $response->body();
                    $mime = $response->header('Content-Type');
                }
            } else {
                $cleanPath = str_replace(['storage/', '/storage/'], '', $path);
                if (Storage::disk('s3')->exists($cleanPath)) {
                    $content = Storage::disk('s3')->get($cleanPath);
                    $mime = Storage::disk('s3')->mimeType($cleanPath);
                }
            }
            return $content ? ['data' => $content, 'mime' => $mime] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function buildFb2Content(Novel $novel): string {
        $binaries = [];
        $chaptersContent = '';

        $coverId = '';
        if ($novel->cover_image) {
            $img = $this->getImageData($novel->cover_image);
            if ($img) {
                $coverId = 'cover.jpg';
                $binaries[$coverId] = base64_encode($img['data']);
            }
        }

        foreach ($novel->chapters as $index => $chapter) {
            $html = ContentRenderer::toHtml($chapter->content ?? '');
            $html = preg_replace_callback('/<img[^>]+src="([^">]+)"[^>]*>/i', function($matches) use (&$binaries) {
                $src = $matches[1];
                $img = $this->getImageData($src);
                if ($img) {
                    $id = 'img_' . md5($src) . '.jpg';
                    $binaries[$id] = base64_encode($img['data']);
                    return '<image l:href="#' . $id . '"/>';
                }
                return '';
            }, $html);

            $text = strip_tags($html, '<p><strong><emphasis><image>');
            $text = str_replace(['<br>', '<p></p>'], [''], $text);
            $title = htmlspecialchars($this->numberedTitle($index + 1, $chapter->title));
            $chaptersContent .= "<section><title><p>{$title}</p></title>{$text}</section>";
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<FictionBook xmlns="http://www.gribuser.ru/xml/fictionbook/2.0" xmlns:l="http://www.w3.org/1999/xlink">';
        $xml .= '<description><title-info>';
        $xml .= '<genre>sf_fantasy</genre>';
        $xml .= '<author><first-name>' . htmlspecialchars($novel->author_name ?? 'Unknown') . '</first-name></author>';
        $xml .= '<book-title>' . htmlspecialchars($novel->title) . '</book-title>';
        if ($coverId) $xml .= '<coverpage><image l:href="#' . $coverId . '"/></coverpage>';
        $xml .= '<lang>ru</lang>';
        $xml .= '<annotation><p>' . htmlspecialchars(strip_tags($novel->description ?? '')) . '</p></annotation>';
        $xml .= '</title-info></description>';
        $xml .= '<body>' . $chaptersContent . '</body>';
        foreach ($binaries as $id => $data) {
            $xml .= '<binary id="' . $id . '" content-type="image/jpeg">' . $data . '</binary>';
        }
        $xml .= '</FictionBook>';
        return $xml;
    }

    public function generateFb2(Novel $novel) {
        $xml = $this->buildFb2Content($novel);
        return response($xml)
            ->header('Content-Type', 'application/fb2+xml')
            ->header('Content-Disposition', 'attachment; filename="' . Str::slug($novel->title) . '.fb2"');
    }

    public function buildEpubToPath(Novel $novel, string $path): void {
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE) !== TRUE) {
            throw new \RuntimeException('Could not create EPUB zip at: ' . $path);
        }

        $zip->addFromString('mimetype', 'application/epub+zip');
        $zip->addFromString('META-INF/container.xml', '<?xml version="1.0"?><container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container"><rootfiles><rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/></rootfiles></container>');

        $manifestItems = [];
        $spineItems = [];
        $tocNavPoints = [];
        $images = [];

        $css = "body { font-family: serif; line-height: 1.5; } img { max-width: 100%; } h1 { text-align: center; }";
        $zip->addFromString('OEBPS/style.css', $css);
        $manifestItems[] = '<item id="style" href="style.css" media-type="text/css"/>';

        if ($novel->cover_image) {
            $img = $this->getImageData($novel->cover_image);
            if ($img) {
                $zip->addFromString('OEBPS/cover.jpg', $img['data']);
                $manifestItems[] = '<item id="cover-image" href="cover.jpg" media-type="' . $img['mime'] . '" properties="cover-image"/>';
                
                $coverHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><title>Cover</title><link rel="stylesheet" type="text/css" href="style.css"/></head><body><div style="text-align:center;"><img src="cover.jpg" alt="Cover"/></div></body></html>';
                $zip->addFromString('OEBPS/cover.xhtml', $coverHtml);
                $manifestItems[] = '<item id="cover" href="cover.xhtml" media-type="application/xhtml+xml"/>';
                $spineItems[] = '<itemref idref="cover"/>';
            }
        }

        foreach ($novel->chapters as $index => $chapter) {
            $htmlContent = ContentRenderer::toHtml($chapter->content ?? '');
            
            $htmlContent = preg_replace_callback('/<img[^>]+src="([^">]+)"[^>]*>/i', function($matches) use ($zip, &$images, &$manifestItems) {
                $src = $matches[1];
                $img = $this->getImageData($src);
                if ($img) {
                    $imgName = 'img_' . md5($src) . '.jpg';
                    if (!in_array($imgName, $images)) {
                        $zip->addFromString('OEBPS/images/' . $imgName, $img['data']);
                        $manifestItems[] = '<item id="' . str_replace('.', '_', $imgName) . '" href="images/' . $imgName . '" media-type="' . $img['mime'] . '"/>';
                        $images[] = $imgName;
                    }
                    return '<img src="images/' . $imgName . '" />';
                }
                return '';
            }, $htmlContent);

            $chapterFilename = "chapter_{$index}.xhtml";
            $displayTitle = $this->numberedTitle($index + 1, $chapter->title);
            $escapedTitle = htmlspecialchars($displayTitle);
            $chapterHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $escapedTitle . '</title><link rel="stylesheet" type="text/css" href="style.css"/></head><body><h2>' . $escapedTitle . '</h2>' . $htmlContent . '</body></html>';

            $zip->addFromString('OEBPS/' . $chapterFilename, $chapterHtml);

            $id = "ch_{$index}";
            $manifestItems[] = '<item id="' . $id . '" href="' . $chapterFilename . '" media-type="application/xhtml+xml"/>';
            $spineItems[] = '<itemref idref="' . $id . '"/>';

            $tocNavPoints[] = '<navPoint id="navPoint-' . ($index + 1) . '" playOrder="' . ($index + 1) . '"><navLabel><text>' . $escapedTitle . '</text></navLabel><content src="' . $chapterFilename . '"/></navPoint>';
        }

        $opf = '<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" unique-identifier="BookId" version="2.0">
    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">
        <dc:title>' . htmlspecialchars($novel->title) . '</dc:title>
        <dc:creator>' . htmlspecialchars($novel->author_name ?? 'Unknown') . '</dc:creator>
        <dc:language>ru</dc:language>
        <dc:description>' . htmlspecialchars(strip_tags($novel->description ?? '')) . '</dc:description>
        <meta name="cover" content="cover-image"/>
    </metadata>
    <manifest>
        <item id="ncx" href="toc.ncx" media-type="application/x-dtbncx+xml"/>
        ' . implode("\n", $manifestItems) . '
    </manifest>
    <spine toc="ncx">
        ' . implode("\n", $spineItems) . '
    </spine>
</package>';
        $zip->addFromString('OEBPS/content.opf', $opf);

        $ncx = '<?xml version="1.0" encoding="UTF-8"?>
<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">
    <head>
        <meta name="dtb:uid" content="urn:uuid:BookId"/>
        <meta name="dtb:depth" content="1"/>
        <meta name="dtb:totalPageCount" content="0"/>
        <meta name="dtb:maxPageNumber" content="0"/>
    </head>
    <docTitle><text>' . htmlspecialchars($novel->title) . '</text></docTitle>
    <navMap>
        ' . implode("\n", $tocNavPoints) . '
    </navMap>
</ncx>';
        $zip->addFromString('OEBPS/toc.ncx', $ncx);

        $zip->close();
    }

    public function generateEpub(Novel $novel) {
        $path = storage_path('app/private/ebooks_tmp/book_' . time() . '.epub');
        @mkdir(dirname($path), 0755, true);
        $this->buildEpubToPath($novel, $path);
        return response()->download($path, Str::slug($novel->title) . '.epub')->deleteFileAfterSend(true);
    }

    public function buildTxtContent(Novel $novel): string {
        $content = "{$novel->title}\n";
        if ($novel->author_name) $content .= "Автор: {$novel->author_name}\n";
        $desc = strip_tags($novel->description ?? '');
        if ($desc) $content .= "\nОписание:\n{$desc}\n";
        $content .= "\n" . str_repeat('=', 20) . "\n\n";
        foreach ($novel->chapters as $index => $chapter) {
            $content .= $this->numberedTitle($index + 1, $chapter->title) . "\n\n";
            $html = ContentRenderer::toHtml($chapter->content ?? '');
            $text = strip_tags(str_replace(['</p>', '<br>', '<br/>'], ["\n\n", "\n", "\n"], $html));
            $content .= trim($text) . "\n\n" . str_repeat('-', 10) . "\n\n";
        }
        return $content;
    }

    public function generateTxt(Novel $novel) {
        $content = $this->buildTxtContent($novel);
        return response($content)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . Str::slug($novel->title) . '.txt"');
    }
}
