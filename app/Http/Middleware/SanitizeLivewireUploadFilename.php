<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class SanitizeLivewireUploadFilename
{
    private const MAX_NAME_LEN = 80;

    public function handle(Request $request, Closure $next): Response
    {
        if (!str_contains($request->path(), 'livewire/upload-file')) {
            return $next($request);
        }

        $this->walk($request->files->all());

        return $next($request);
    }

    private function walk(array $files): void
    {
        foreach ($files as $entry) {
            if (is_array($entry)) {
                $this->walk($entry);
                continue;
            }
            if ($entry instanceof UploadedFile) {
                $this->shorten($entry);
            }
        }
    }

    private function shorten(UploadedFile $file): void
    {
        $original = $file->getClientOriginalName();
        if (strlen($original) <= self::MAX_NAME_LEN) {
            return;
        }

        $ext  = $file->getClientOriginalExtension();
        $stub = 'upload-' . substr(sha1($original), 0, 10);
        $new  = $ext ? $stub . '.' . $ext : $stub;

        try {
            $r = new \ReflectionObject($file);
            $prop = $r->hasProperty('originalName')
                ? $r->getProperty('originalName')
                : null;
            if ($prop) {
                $prop->setAccessible(true);
                $prop->setValue($file, $new);
            }
        } catch (\Throwable $e) {
            \Log::warning('SanitizeLivewireUploadFilename failed: ' . $e->getMessage());
        }
    }
}
